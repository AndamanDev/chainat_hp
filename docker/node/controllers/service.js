const { TbService } = require('../models')
const { ServiceQuerier } = require('../models/queryql')

exports.getServices = async (req, res) => {
  try {
    const services = await TbService.find()
    res.json(services)
    // const querier = new ServiceQuerier(req.query, TbService.find())
    // const response = await querier.run()
    // res.json(response)
  } catch (error) {
    res.error(error)
  }
}

/**
 * @param {number} id
 */
exports.getServicesById = async (req, res) => {
  try {
    req.assert(req.params.id, 400, `invalid serviceid.`)
    const services = await TbService.find().where({ serviceid: req.params.id })
    res.json(services)
  } catch (error) {
    res.error(error)
  }
}