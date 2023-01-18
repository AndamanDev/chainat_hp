'use strict'

const path = require('path')
const fs = require('fs')
const pino = require('pino')
const rfs = require('rotating-file-stream')
const config = require('../config')
const rootPath = path.dirname(__dirname)

if (!fs.existsSync(path.join(rootPath, 'logs', 'message'))) {
  fs.mkdirSync(path.join(rootPath, 'logs', 'message'))
  fs.writeFileSync(path.join(rootPath, 'logs', 'message', '.gitignore'), '*\n!.gitignore')
}

// create a rotating write stream
const accessLogStream = rfs.createStream('message.log', {
  interval: process.env.LOG_INTERVAL, // rotate daily
  path: path.join(rootPath, 'logs', 'message'),
  size: process.env.LOG_FILE_SIZE,
})

const errorLogStream = rfs.createStream('error-message.log', {
  interval: process.env.LOG_INTERVAL, // rotate daily
  path: path.join(rootPath, 'logs', 'message'),
  size: process.env.LOG_FILE_SIZE,
})

const messageLogger = pino(config.pinoLogger, accessLogStream)
const errorLogger = pino(config.pinoLogger, errorLogStream)

module.exports = {
  messageLogger,
  errorLogger,
}