var express = require("express")
var qs = require("qs")
var router = express.Router()
const axios = require("axios")
axios.defaults.baseURL = "http://nginx"
//axios.defaults.baseURL = "http://queue-chainat.local";
const config = {
	baseURL: "http://nginx"
}

router.get("/calling-queue", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/calling-queue" + params, config)
		req.io.emit("call", response.data)
		res.send(response.data)
	} catch (error) {
		res.status(error.response.status || 500).send(error.response.data)
	}
})

router.get("/hold-queue", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/hold-queue" + params, config)
		req.io.emit("hold", response.data)
		res.send(response.data)
	} catch (error) {
		res.status(error.response.status || 500).send(error.response.data)
	}
})

router.get("/end-queue", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/end-queue" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.status(error.response.status || 500).send(error.response.data)
	}
})

router.get("/send-to-doctor", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/send-to-doctor" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.status(error.response.status || 500).send(error.response.data)
	}
})

router.get("/waiting-doctor", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/waiting-doctor-queue" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.status(error.response.status || 500).send(error.response.data)
	}
})

router.get("/waiting-pharmacy", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/waiting-pharmacy-queue" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.status(error.response.status || 500).send(error.response.data)
	}
})

module.exports = router
