const path = require('path')
const rfs = require('rotating-file-stream')
const rootPath = path.dirname(__dirname)

// create a rotating write stream
const errorLogStream = rfs.createStream('error.log', {
  interval: process.env.LOG_INTERVAL, // rotate daily
  path: path.join(rootPath, 'logs'),
  size: process.env.LOG_FILE_SIZE,
})

module.exports = errorLogStream
