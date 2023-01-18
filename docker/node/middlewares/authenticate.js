const { User } = require('../models')
const _ = require('lodash')

module.exports = async (req, res, next) => {
  try {
    req.assert(_.get(req.query, 'access-token'), 401, "Unauthorized.")
    const user = await User.findOne({ auth_key: _.get(req.query, 'access-token') })
    req.assert(user, 401, "Unauthorized.")
    req.user = _.pick(user, ['id', 'username', 'last_login_at'])
    next()
  } catch (error) {
    res.error(error)
  }
}