const { TbService  } = require('../index')
const BaseQuerier = require('./base-querier')
const operators = require('./operators')
const attributes = Object.keys(TbService.attributeLabels())
class Querier extends BaseQuerier {
  defineSchema(schema) {
    // filtering
    schema.filter('q', '=')
    for (let i = 0; i < attributes.length; i++) {
      const field = attributes[i];
      schema.filter(field, operators, { field: `${TbService.tableName}.${field}` })
      schema.sort(field, { field: `${TbService.tableName}.${field}` })
    }
    schema.page(!!this.query.page)
  }

  'filter:q[=]'(builder, { value }) {
    value = `%${value}%`
    return builder.where(function () {
      for (let i = 0; i < attributes.length; i++) {
        const field = attributes[i];
        this.orWhere(`${TbService.tableName}.${field}`, 'like', value)
      }
      // this.orWhere('tb_gate.gate_id', 'like', value)
    })
  }
}

module.exports = Querier
