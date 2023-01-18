const { TbDrugConfig } = require('../models')
const { DrugConfigQuerier } = require('../models/queryql')

exports.getDrugsConfig = async (req, res) => {
  try {
    const counters = await TbDrugConfig.find()
    res.json(counters)
  } catch (error) {
    res.error(error)
  }
}

/**
 * @param {number} id
 */
exports.getDrugsConfigById = async (req, res) => {
  try {
    req.assert(req.params.id, 400, `invalid id.`)
    const counters = await TbDrugConfig.find().where({ id: req.params.id })
    res.json(counters)
  } catch (error) {
    res.error(error)
  }
}