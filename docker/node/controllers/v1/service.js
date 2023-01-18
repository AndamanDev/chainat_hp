const { TbService } = require('../../models')
const { ServiceQuerier } = require('../../models/queryql')

exports.getServices = async (req, res) => {
  try {
    const querier = new ServiceQuerier(req.query, TbService.find())
    const response = await querier.run()
    res.success(response)
  } catch (error) {
    res.error(error)
  }
}