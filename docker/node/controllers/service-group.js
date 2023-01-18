const { TbServiceGroup } = require('../models')
const { ServiceGroupQuerier } = require('../models/queryql')

exports.getServiceGroups = async (req, res) => {
  try {
    const services = await TbServiceGroup.find()
    res.json(services)
  } catch (error) {
    res.error(error)
  }
}

/**
 * @param {number} id
 */
exports.getServiceGroupsById = async (req, res) => {
  try {
    req.assert(req.params.id, 400, `invalid servicegroupid.`)
    const services = await TbServiceGroup.find().where({ servicegroupid: req.params.id })
    res.json(services)
  } catch (error) {
    res.error(error)
  }
}