const { TbDrugConfig } = require('../../models')
const { DrugConfigQuerier } = require('../../models/queryql')

exports.getDrugsConfig = async (req, res) => {
  try {
    const querier = new DrugConfigQuerier(req.query, TbDrugConfig.find())
    const response = await querier.run()
    res.success(response)
  } catch (error) {
    res.error(error)
  }
}