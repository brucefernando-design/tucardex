import express from 'express';
import cors from 'cors';
import pino from 'pino';
import QRCode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import makeWASocket, {
  DisconnectReason,
  useMultiFileAuthState,
  fetchLatestBaileysVersion
} from '@whiskeysockets/baileys';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 3000;
const SESSIONS_DIR = path.join(__dirname, 'sessions');

if (!fs.existsSync(SESSIONS_DIR)) {
  fs.mkdirSync(SESSIONS_DIR, { recursive: true });
}

const instances = new Map(); // schoolId -> { sock, qr, status, phone, name }

const logger = pino({ level: 'warn' });

// Normaliza números mexicanos para WhatsApp (+52 1 XXXXXXXXXX o +52 XXXXXXXXXX)
function formatWhatsAppJid(phone) {
  let cleaned = phone.toString().replace(/\D/g, '');
  
  // Si viene con 10 dígitos (México local), agregar código 52
  if (cleaned.length === 10) {
    cleaned = '521' + cleaned;
  } else if (cleaned.startsWith('52') && cleaned.length === 12 && !cleaned.startsWith('521')) {
    // Si viene 52 y luego 10 dígitos (ej 528671234567), WhatsApp México usa prefijo móvil 521
    cleaned = '521' + cleaned.substring(2);
  }
  
  return `${cleaned}@s.whatsapp.net`;
}

async function initInstance(schoolId) {
  if (instances.has(schoolId)) {
    const existing = instances.get(schoolId);
    if (existing.status === 'connected' && existing.sock) {
      return existing;
    }
  }

  const sessionFolder = path.join(SESSIONS_DIR, `school_${schoolId}`);
  if (!fs.existsSync(sessionFolder)) {
    fs.mkdirSync(sessionFolder, { recursive: true });
  }

  const { state, saveCreds } = await useMultiFileAuthState(sessionFolder);
  const { version } = await fetchLatestBaileysVersion();

  const instanceData = {
    sock: null,
    qr: null,
    status: 'connecting',
    phone: null,
    name: null
  };
  instances.set(schoolId, instanceData);

  const sock = makeWASocket({
    version,
    auth: state,
    logger,
    printQRInTerminal: false,
    defaultQueryTimeoutMs: 60000,
    browser: ['TuCardex Escolar', 'Chrome', '1.0.0']
  });

  instanceData.sock = sock;

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      try {
        instanceData.qr = await QRCode.toDataURL(qr, { margin: 2, scale: 7 });
        instanceData.status = 'scan_qr';
      } catch (err) {
        console.error(`Error generando QR para escuela ${schoolId}:`, err);
      }
    }

    if (connection === 'open') {
      instanceData.qr = null;
      instanceData.status = 'connected';
      const user = sock.user;
      instanceData.phone = user?.id ? user.id.split(':')[0] : null;
      instanceData.name = user?.name || 'Colegio Conectado';
      console.log(`[TuCardex WA] Escuela ${schoolId} CONECTADA exitosamente (${instanceData.phone})`);
    }

    if (connection === 'close') {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
      
      console.log(`[TuCardex WA] Conexión cerrada para escuela ${schoolId}, código: ${statusCode}. Reconectar: ${shouldReconnect}`);
      
      instanceData.status = 'disconnected';
      instanceData.qr = null;

      if (shouldReconnect) {
        setTimeout(() => initInstance(schoolId), 5000);
      } else {
        // Sesión cerrada formalmente (logout)
        try {
          fs.rmSync(sessionFolder, { recursive: true, force: true });
        } catch (e) {}
        instances.delete(schoolId);
      }
    }
  });

  return instanceData;
}

// Auto-restaurar sesiones guardadas al iniciar el microservicio
async function restoreSavedSessions() {
  try {
    const folders = fs.readdirSync(SESSIONS_DIR);
    for (const folder of folders) {
      if (folder.startsWith('school_')) {
        const schoolId = folder.replace('school_', '');
        console.log(`[TuCardex WA] Restaurando sesión existente para escuela ${schoolId}...`);
        initInstance(schoolId).catch((err) => {
          console.error(`Error restaurando sesión para escuela ${schoolId}:`, err);
        });
      }
    }
  } catch (e) {
    console.error('Error restaurando sesiones:', e);
  }
}

// ---------------- ENDPOINTS REST ----------------

app.get('/health', (req, res) => {
  res.json({ ok: true, timestamp: new Date().toISOString() });
});

// Consulta el estado de la conexión de la escuela
app.get('/status/:schoolId', async (req, res) => {
  const { schoolId } = req.params;
  let inst = instances.get(schoolId);

  if (!inst) {
    const sessionFolder = path.join(SESSIONS_DIR, `school_${schoolId}`);
    if (fs.existsSync(sessionFolder)) {
      inst = await initInstance(schoolId);
    }
  }

  res.json({
    ok: true,
    schoolId,
    status: inst?.status || 'disconnected',
    phone: inst?.phone || null,
    name: inst?.name || null
  });
});

// Obtiene el Código QR para escanear
app.get('/qr/:schoolId', async (req, res) => {
  const { schoolId } = req.params;
  let inst = instances.get(schoolId);

  if (!inst || inst.status === 'disconnected') {
    inst = await initInstance(schoolId);
  }

  if (inst.status === 'connected') {
    return res.json({
      ok: true,
      status: 'connected',
      phone: inst.phone,
      qr: null
    });
  }

  // Esperar hasta 4 segundos a que se genere el QR si está iniciando
  let attempts = 0;
  while (!inst.qr && attempts < 8 && inst.status !== 'connected') {
    await new Promise((r) => setTimeout(r, 500));
    attempts++;
  }

  res.json({
    ok: true,
    status: inst.status,
    phone: inst.phone,
    qr: inst.qr
  });
});

// Envía un mensaje de WhatsApp
app.post('/send/:schoolId', async (req, res) => {
  const { schoolId } = req.params;
  const { phone, message } = req.body;

  if (!phone || !message) {
    return res.status(400).json({ ok: false, error: 'Faltan parámetros phone o message' });
  }

  const inst = instances.get(schoolId);
  if (!inst || inst.status !== 'connected' || !inst.sock) {
    return res.status(400).json({
      ok: false,
      error: 'WhatsApp de la escuela no está conectado. Debe escanear el código QR primero.'
    });
  }

  try {
    const jid = formatWhatsAppJid(phone);
    const sent = await inst.sock.sendMessage(jid, { text: message });
    
    res.json({
      ok: true,
      messageId: sent.key.id,
      to: jid,
      timestamp: new Date().toISOString()
    });
  } catch (err) {
    console.error(`Error enviando mensaje a ${phone} (Escuela ${schoolId}):`, err);
    res.status(500).json({
      ok: false,
      error: err.message || 'Error al enviar mensaje de WhatsApp'
    });
  }
});

// Cierra sesión y borra credenciales de la escuela
app.post('/logout/:schoolId', async (req, res) => {
  const { schoolId } = req.params;
  const inst = instances.get(schoolId);

  if (inst && inst.sock) {
    try {
      await inst.sock.logout();
    } catch (e) {}
  }

  const sessionFolder = path.join(SESSIONS_DIR, `school_${schoolId}`);
  try {
    fs.rmSync(sessionFolder, { recursive: true, force: true });
  } catch (e) {}

  instances.delete(schoolId);

  res.json({ ok: true, message: 'Sesión de WhatsApp cerrada exitosamente' });
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`[TuCardex WA Gateway] Microservicio iniciado en puerto ${PORT}`);
  restoreSavedSessions();
});
