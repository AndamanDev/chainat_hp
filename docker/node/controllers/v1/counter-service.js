const { TbCounterService } = require('../../models')
const { CounterServiceQuerier } = require('../../models/queryql')

exports.getCounterServices = async (req, res) => {
  try {
    const querier = new CounterServiceQuerier(req.query, TbCounterService.find())
    const response = await querier.run()
    res.success(response)
  } catch (error) {
    res.error(error)
  }
}