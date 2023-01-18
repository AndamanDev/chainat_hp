'use strict'

const path = require('path')
const fs = require('fs')
const pino = require('pino')
const rfs = require('rotating-file-stream')
const rootPath = path.dirname(__dirname)
const savePath = path.join(rootPath, 'logs', 'redis')
const config = require("../config")

if (!fs.existsSync(savePath)) {
  fs.mkdirSync(savePath)
  fs.writeFileSync(path.join(rootPath, 'logs', 'redis', '.gitignore'), '*\n!.gitignore')
}

// create a rotating write stream
const accessLogStream = rfs.createStream('access.log', {
  interval: '1d', // rotate daily
  path: savePath,
  size: '10M',
})

const errorLogStream = rfs.createStream('error.log', {
  interval: '1d', // rotate daily
  path: savePath,
  size: '10M',
})

const accessLogger = pino(config.pinoLogger, accessLogStream)
const errorLogger = pino(config.pinoLogger, errorLogStream)

module.exports = {
  accessLogger,
  errorLogger,
}
