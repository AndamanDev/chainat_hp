var express = require("express");
var qs = require("qs");
var router = express.Router();
const axios = require("axios");
const config = {
  baseURL: "http://nginx",
};

router.post("/create-queue", async function(req, res) {
  try {
    const response = await axios.post("/app/kiosk/create-queue", req.body, config);
    req.io.emit("register", response.data);
    res.send(response.data);
  } catch (error) {
    res.status(error.response.status || 500).send(error.response.data);
  }
});
module.exports = router;