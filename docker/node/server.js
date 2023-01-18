require("dotenv").config();

const app = require("express")();
const session = require('express-session')
const RedisStore = require('connect-redis')(session)
const redisClient = require('./config/redis')
const multiparty = require("multiparty");
const cors = require("cors");
const corsMiddleware = require('./middlewares/cors')
const assignId = require('./middlewares/assign-id')
const compression = require('compression')
const helmet = require("helmet");
const RateLimitRedis = require('rate-limit-redis')
const RateLimit = require('express-rate-limit')
const bodyParser = require("body-parser");
const responseTime = require('response-time')
const pinoHttp = require('pino-http')
const pino = require('pino')
const customResponse = require('./middlewares/custom-response')
const accessLogStream = require('./logger/access-stream')
const config = require('./config')
const cron = require('node-cron');
const glob = require("glob")
const path = require('path')
const fs = require('fs')
const passport = require('passport')
const server = require("http").Server(app);
const io = require("socket.io")(server, {
  allowEIO3: true,
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});
const port = process.env.PORT || 3000;
const host = process.env.HOST || '0.0.0.0';
const error = require('./middlewares/error')
const ioclient = require("socket.io-client");
const socketclient = ioclient("http://localhost:3000", { path: "/socket.io" });
// const socketclient = ioclient("http://nginx", { path: "/node/socket.io" });
const admin = require("firebase-admin");

const serviceAccount = require("./chainathos-ef609-firebase-adminsdk-r7eqo-3cfdbddd2d.json");

admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
  databaseURL: "https://chainathos-ef609-default-rtdb.asia-southeast1.firebasedatabase.app",
});
//const socketclient = ioclient("http://nginx", { path: "/node/socket.io" });

socketclient
  .on("connect", () => {
    console.log("connected"); // true
  })
  .on("error", (error) => {
    console.log(error); // "G5p5..."
  });

app.use(assignId);
app.use(compression())
app.use(
  helmet({
    contentSecurityPolicy: false,
  })
)
app.use(cors());
app.use(corsMiddleware);

/**
 * Logger
 */
const logger = require('pino-http')({
  logger: pino(config.pinoLogger, accessLogStream),
  genReqId: (req) => {
    return req.id
  },
  customLogLevel: function (req, res, err) {
    if (res.statusCode >= 400 && res.statusCode < 500) {
      return 'warn'
    } else if (res.statusCode >= 500 || err) {
      return 'error'
    }
    return 'info'
  },
  serializers: {
    req(req) {
      req.body = req.body;
      return req;
    },
  },
})

app.use(function (req, res, next) {
  req.io = socketclient;
  logger(req, res)
  next();
});
/**
 * Rate Limit
 */
const limiter = new RateLimit({
  store: new RateLimitRedis({ client: redisClient }),
  max: 5000, // limit each IP to 100 requests per windowMs
  delayMs: 0, // disable delaying - full speed until the max limit is reached
})
app.use(limiter)

app.use(bodyParser.urlencoded({ extended: true, limit: process.env.BODY_REQUEST_LIMIT || '100mb' }));
app.use(bodyParser.json({ limit: process.env.BODY_REQUEST_LIMIT || '100mb' }));
app.use(
  session({
    store: new RedisStore({ client: redisClient }),
    secret: process.env.SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: {
      maxAge: 24 * 60 * 60 * 1000,
      // httpOnly: true,
      // secure: true,
      // domain: process.env.BASE_URL,
      // //  new Date(Date.now() + 60 * 60 * 1000) Cookie will expire in 1 hour from when it's generated
      // expires: 24 * 60 * 60 * 1000, // 24 hours
    },
    name: process.env.SESSION_NAME,
  })
)
require('./config/passport')
app.use(passport.initialize());
app.use(passport.session());

/**
 * Response time
 */
app.use(responseTime())

/**
 * custom response
 */
app.use(customResponse())

app.get("/", function (req, res) {
  res.sendFile(__dirname + "/index.html");
});

const indexRouter = require("./routes/index");
const callingRouter = require("./routes/calling");
const dispensingRouter = require("./routes/dispensing");
const kioskRouter = require("./routes/v1/kiosk");
const callingV1Router = require("./routes/v1/calling");
const examinationV1Router = require("./routes/v1/examination");
const v1Router = require("./routes/v1");
const messageQueue = require("./jobs")(admin);

app.use("/api", indexRouter);
app.use("/v1", v1Router);
app.use("/api/calling", callingRouter);
app.use("/api/dispensing", dispensingRouter);
app.use("/api/kiosk", kioskRouter);
app.use("/api/v1/calling", callingV1Router);
app.use("/api/v1/examination", examinationV1Router);

app.post("/api/send-message", async function (req, res) {
  try {
    await admin.messaging().send(req.body.message);

    res.status(200).send({ message: "Successfully sent message." });
  } catch (error) {
    res.status(500).send(error);
  }
});

app.post("/api/add-message", async function (req, res) {
  try {
    await messageQueue.add(req.body.message);
    res.status(200).send({ message: "Successfully sent message." });
  } catch (error) {
    res.status(500).send(error);
  }
});

const getClientIp = (socket) => {
  return socket.handshake.headers["x-forwarded-for"]
    ? socket.handshake.headers["x-forwarded-for"].split(/\s*,\s*/)[0]
    : socket.request.connection.remoteAddress;
};

const EVENTS = {
  PCSC_INITIAL: "PCSC_INITIAL",
  PCSC_CLOSE: "PCSC_CLOSE",

  DEVICE_WAITING: "DEVICE_WAITING",
  DEVICE_CONNECTED: "DEVICE_CONNECTED",
  DEVICE_ERROR: "DEVICE_ERROR",
  DEVICE_DISCONNECTED: "DEVICE_DISCONNECTED",

  CARD_INSERTED: "CARD_INSERTED",
  CARD_REMOVED: "CARD_REMOVED",

  READING_INIT: "READING_INIT",
  READING_START: "READING_START",
  READING_PROGRESS: "READING_PROGRESS",
  READING_COMPLETE: "READING_COMPLETE",
  READING_FAIL: "READING_FAIL",
};

//connection
io.on("connection", function (socket) {
  //ลงทะเบียนผู้ป่วย
  socket.on("register", function (res) {
    socket.broadcast.emit("register", res);
  });

  //เรียกคิว
  socket.on("call-screening-room", function (res) {
    socket.broadcast.emit("call-screening-room", res);
  });

  //Hold คิว
  socket.on("hold-screening-room", function (res) {
    socket.broadcast.emit("hold-screening-room", res);
  });

  //End คิว
  socket.on("endq-screening-room", function (res) {
    socket.broadcast.emit("endq-screening-room", res);
  });

  //เรียกคิว
  socket.on("call-examination-room", function (res) {
    socket.broadcast.emit("call-examination-room", res);
  });

  //Hold คิว
  socket.on("hold-examination-room", function (res) {
    socket.broadcast.emit("hold-examination-room", res);
  });

  //End คิว
  socket.on("endq-examination-room", function (res) {
    socket.broadcast.emit("endq-examination-room", res);
  });

  //เรียกคิว
  socket.on("call-medicine-room", function (res) {
    socket.broadcast.emit("call-medicine-room", res);
  });

  //Hold คิว
  socket.on("hold-medicine-room", function (res) {
    socket.broadcast.emit("hold-medicine-room", res);
  });

  //End คิว
  socket.on("endq-medicine-room", function (res) {
    socket.broadcast.emit("endq-medicine-room", res);
  });

  socket.on("transfer-examination-room", function (res) {
    socket.broadcast.emit("transfer-examination-room", res);
  });
  //สร้างรายการรับยาใกล้บ้าน
  socket.on("create-drug-dispensing", function (res) {
    io.emit("create-drug-dispensing", res);
  });

  //Display
  socket.on("display", function (res) {
    socket.broadcast.emit("display", res);
  });

  socket.on("call", function (res) {
    socket.broadcast.emit("call", res);
  });
  socket.on("recall", function (res) {
    socket.broadcast.emit("recall", res);
  });
  socket.on("hold", function (res) {
    socket.broadcast.emit("hold", res);
  });
  socket.on("finish", function (res) {
    socket.broadcast.emit("finish", res);
  });

  socket.on("get ip", (clientId) => {
    socket.emit("ip", { ip: getClientIp(socket), clientId: clientId });
  });

  socket.on("join-room", (config) => {
    const roomId = getClientIp(socket);

    socket.join(roomId);
    socket.broadcast.to(roomId).emit("user-connected", config);

    socket.on("message", (message) => {
      io.to(roomId).emit("createMessage", message, config);
    });

    socket.on(EVENTS.DEVICE_CONNECTED, (data) => {
      io.to(roomId).emit(EVENTS.DEVICE_CONNECTED, config);
    });

    socket.on(EVENTS.DEVICE_DISCONNECTED, (data) => {
      io.to(roomId).emit(EVENTS.DEVICE_DISCONNECTED, config);
    });

    socket.on(EVENTS.CARD_INSERTED, (data) => {
      io.to(roomId).emit(EVENTS.CARD_INSERTED, config);
    });

    socket.on(EVENTS.CARD_REMOVED, (data) => {
      io.to(roomId).emit(EVENTS.CARD_REMOVED, config);
    });

    socket.on(EVENTS.READING_START, (data) => {
      io.to(roomId).emit(EVENTS.READING_START, config);
    });

    socket.on(EVENTS.READING_COMPLETE, (data) => {
      io.to(roomId).emit(EVENTS.READING_COMPLETE, data, config);
    });

    socket.on(EVENTS.READING_FAIL, (data) => {
      io.to(roomId).emit(EVENTS.READING_FAIL, config);
    });

    socket.on("disconnect", () => {
      socket.broadcast.to(roomId).emit("user-disconnected", config);
    });
  });

  app.post("/api/save-profile", function (req, res) {
    if (!req.body) return res.sendStatus(400);

    var form = new multiparty.Form();

    form.on("error", function (err) {
      console.log("Error parsing form: " + err.stack);
    });

    form.parse(req, function (err, fields, files) {
      //console.log('fields: %@', fields);
      socket.broadcast.emit("read-card", fields);
    });

    req.on("end", () => {
      res.send("ok");
    });
  });

  socket.on("disconnect", function () {
    io.emit("disconnected");
  });
});

// if error is not an instanceOf APIError, convert it.
app.use(error.converter)

// catch 404 and forward to error handler
app.use(error.notFound)

// error handler, send stacktrace only during development
app.use(error.handler)

cron.schedule('0 1 * * 7', () => {
  glob("./logs/**/*.log", {}, async (er, files) => {
    if (files.length) {
      for (let i = 0; i < files.length; i++) {
        const filePath = files[i];
        const filename = path.basename(filePath)
        if (!['error-message.log', 'message.log', 'access.log', 'error.log'].includes(filename)) {
          fs.unlinkSync(filePath);
        }
      }
    }
  })
});

server.listen(port, host, function () {
  console.log(`listening on http://${host}:${port}`);
});
