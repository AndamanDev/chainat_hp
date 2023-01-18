'use strict'

const redis = require('redis')
const logger = require('../logger/redis')
const devMode = process.env.NODE_ENV === 'development'

let config = {
  host: process.env.REDIS_HOST,
  port: process.env.REDIS_PORT,
}
if (!devMode) {
  config = Object.assign(config, { password: process.env.REDIS_PASSWORD })
}

const redisClient = redis.createClient(config)

redisClient.on('error', function (error) {
  logger.errorLogger.warn(error)
})

module.exports = redisClient
