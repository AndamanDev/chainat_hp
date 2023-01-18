const { TbServiceGroup } = require('../../models')
const { ServiceGroupQuerier } = require('../../models/queryql')

exports.getServiceGroups = async (req, res) => {
  try {
    const querier = new ServiceGroupQuerier(req.query, TbServiceGroup.find())
    const response = await querier.run()
    res.success(response)
  } catch (error) {
    res.error(error)
  }
}