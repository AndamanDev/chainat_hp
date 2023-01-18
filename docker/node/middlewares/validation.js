'use strict'

const yup = require('yup')
const _ = require('lodash')

module.exports = (validation = {}) => {
  return async (req, res, next) => {
    try {
      // validate data
      const schema = yup.object().shape(validation)
      await schema.validate(Object.assign(req.body, req.query, req.params))
      next()
    } catch (error) {
      next(error)
    }
  }
}
