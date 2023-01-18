'use strict'

/**
 * custom response
 * Example.
 * app.get("/", (req, res) => res.success({ message: "It works!" }))
 * app.get("/", (req, res) => res.error({ error: "data not found." }))
 * app.get("/401", (req, res) => req.throw(401))
 * app.get("/400", (req, res) => req.throw(400, 'name required'))
 * app.get("/400", (req, res) => req.throw(400, 'name required', { user: user }))
 * app.get("/401", (req, res) => req.assert(req.user, 401, 'User not found. Please login!'))
 */

const createError = require('http-errors')
const httpAssert = require('http-assert')
// const APIError = require('../utils/APIError')
const { converter } = require('./error')
// const { ValidationError } = require('yup')
// error
const throwError = (...args) => {
  throw createError(...args)
}

module.exports = () => {
  return function (req, res, next) {
    req.assert = httpAssert
    req.throw = throwError

    const success = (data = '', statusCode = 200) => {
      res.status(statusCode || 200).send({
        success: true,
        statusCode: statusCode,
        message: 'ok',
        data: data,
      })
    }
    res.success = success
    res.sendSuccess = success

    const error = (err) => {
      /* let statusCode = err.status || 500
      const convertedError = new APIError({
        message: err.message,
        status: statusCode,
        stack: err.stack,
      }) */
      converter(err, req, res, next)
    }

    res.error = error
    res.sendError = error
    next()
  }
}
