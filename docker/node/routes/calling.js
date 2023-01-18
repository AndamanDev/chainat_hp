var express = require("express")
var qs = require("qs")
var router = express.Router()
const axios = require("axios")
const _ = require('lodash')
axios.defaults.baseURL = process.env.WEB_BASE_URL
//axios.defaults.baseURL = "http://queue-chainat.local";
const config = {
	baseURL: process.env.WEB_BASE_URL
}

router.get("/calling-queue", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/calling-queue" + params, config)
		req.io.emit("call", response.data)
		res.send(response.data)
	} catch (error) {
		res.error(error)
	}
})

router.get("/hold-queue", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/hold-queue" + params, config)
		req.io.emit("hold", response.data)
		res.send(response.data)
	} catch (error) {
		res.error(error)
	}
})

router.get("/end-queue", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/end-queue" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.error(error)
	}
})

router.get("/send-to-doctor", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/send-to-doctor" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.error(error)
	}
})

router.get("/waiting-doctor", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/waiting-doctor-queue" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.error(error)
	}
})

router.get("/waiting-pharmacy", async function (req, res) {
	try {
		const params = `?${qs.stringify(req.query)}`
		const response = await axios.get("/app/calling/waiting-pharmacy-queue" + params, config)
		req.io.emit("finish", response.data)
		res.send(response.data)
	} catch (error) {
		res.error(error)
	}
})

module.exports = router
