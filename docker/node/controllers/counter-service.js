const { TbCounterService } = require('../models')
const { CounterServiceQuerier } = require('../models/queryql')

exports.getCounterServices = async (req, res) => {
  try {
    const counters = await TbCounterService.find()
    res.json(counters)
  } catch (error) {
    res.error(error)
  }
}

/**
 * @param {number} id
 */
exports.getCounterServicesById = async (req, res) => {
  try {
    req.assert(req.params.id, 400, `invalid counterserviceid.`)
    const counters = await TbCounterService.find().where({ counterserviceid: req.params.id })
    res.json(counters)
  } catch (error) {
    res.error(error)
  }
}