const pino = require('pino')
const config = require('../config')
const errorLogStream = require('./error-stream')
const accessLogStream = require('./access-stream')
const messageLogger = require('./message')

const errorLogger = pino(config.pinoLogger, errorLogStream)
const accessLogger = pino(config.pinoLogger, accessLogStream)

module.exports = {
  errorLogger,
  accessLogger,
  messageLogger
}
