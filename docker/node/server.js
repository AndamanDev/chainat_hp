require("dotenv").config();
var app = require("express")();
var cors = require("cors");
var server = require("http").Server(app);
var io = require("socket.io")(server, {
  allowEIO3: true,
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});
var port = process.env.PORT || 3000;
var bodyParser = require("body-parser");
const ioclient = require("socket.io-client");
const socketclient = ioclient("http://localhost:3000", { path: "/socket.io" });
// const socketclient = ioclient("http://nginx", { path: "/node/socket.io" });
const admin = require("firebase-admin");

var serviceAccount = require("./chainathos-ef609-firebase-adminsdk-r7eqo-3cfdbddd2d.json");

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

// require the module

var multiparty = require("multiparty");
app.use(cors());
app.use(bodyParser.urlencoded({ extended: false }));

app.use(bodyParser.json());

app.get("/", function(req, res) {
  res.sendFile(__dirname + "/index.html");
});
app.use(function(req, res, next) {
  res.header("Access-Control-Allow-Origin", "*");
  res.header("Access-Control-Allow-Methods", "GET,PUT,POST,DELETE");
  res.header("Access-Control-Allow-Headers", "Content-Type");

  req.io = socketclient;
  next();
});

var indexRouter = require("./routes/index");
var callingRouter = require("./routes/calling");
var dispensingRouter = require("./routes/dispensing");
var kioskRouter = require("./routes/kiosk");
var messageQueue = require("./jobs")(admin);

app.use("/api", indexRouter);
app.use("/api/calling", callingRouter);
app.use("/api/dispensing", dispensingRouter);
app.use("/api/kiosk", kioskRouter);

app.post("/api/send-message", async function(req, res) {
  try {
    await admin.messaging().send(req.body.message);

    res.status(200).send({ message: "Successfully sent message." });
  } catch (error) {
    res.status(500).send(error);
  }
});

app.post("/api/add-message", async function(req, res) {
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
io.on("connection", function(socket) {
  //ลงทะเบียนผู้ป่วย
  socket.on("register", function(res) {
    socket.broadcast.emit("register", res);
  });

  //เรียกคิว
  socket.on("call-screening-room", function(res) {
    socket.broadcast.emit("call-screening-room", res);
  });

  //Hold คิว
  socket.on("hold-screening-room", function(res) {
    socket.broadcast.emit("hold-screening-room", res);
  });

  //End คิว
  socket.on("endq-screening-room", function(res) {
    socket.broadcast.emit("endq-screening-room", res);
  });

  //เรียกคิว
  socket.on("call-examination-room", function(res) {
    socket.broadcast.emit("call-examination-room", res);
  });

  //Hold คิว
  socket.on("hold-examination-room", function(res) {
    socket.broadcast.emit("hold-examination-room", res);
  });

  //End คิว
  socket.on("endq-examination-room", function(res) {
    socket.broadcast.emit("endq-examination-room", res);
  });

  //เรียกคิว
  socket.on("call-medicine-room", function(res) {
    socket.broadcast.emit("call-medicine-room", res);
  });

  //Hold คิว
  socket.on("hold-medicine-room", function(res) {
    socket.broadcast.emit("hold-medicine-room", res);
  });

  //End คิว
  socket.on("endq-medicine-room", function(res) {
    socket.broadcast.emit("endq-medicine-room", res);
  });

  socket.on("transfer-examination-room", function(res) {
    socket.broadcast.emit("transfer-examination-room", res);
  });
  //สร้างรายการรับยาใกล้บ้าน
  socket.on("create-drug-dispensing", function(res) {
    io.emit("create-drug-dispensing", res);
  });

  //Display
  socket.on("display", function(res) {
    socket.broadcast.emit("display", res);
  });

  socket.on("call", function(res) {
    socket.broadcast.emit("call", res);
  });
  socket.on("recall", function(res) {
    socket.broadcast.emit("recall", res);
  });
  socket.on("hold", function(res) {
    socket.broadcast.emit("hold", res);
  });
  socket.on("finish", function(res) {
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

  app.post("/api/save-profile", function(req, res) {
    if (!req.body) return res.sendStatus(400);

    var form = new multiparty.Form();

    form.on("error", function(err) {
      console.log("Error parsing form: " + err.stack);
    });

    form.parse(req, function(err, fields, files) {
      //console.log('fields: %@', fields);
      socket.broadcast.emit("read-card", fields);
    });

    req.on("end", () => {
      res.send("ok");
    });
  });

  socket.on("disconnect", function() {
    io.emit("disconnected");
  });
});

server.listen(port, function() {
  console.log("listening on *:" + port);
});
