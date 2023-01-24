const { TbService, TbServiceGroup, TbCounterService, TbCounterServiceType } = require('../index')
const BaseQuerier = require('./base-querier')
const operators = require('./operators')
const serviceattributes = Object.keys(TbService.attributeLabels())
const groupattributes = Object.keys(TbServiceGroup.attributeLabels())
const typeattributes = Object.keys(TbCounterService.attributeLabels())
const servicetypeattributes = Object.keys(TbCounterServiceType.attributeLabels())

class Querier extends BaseQuerier {
  defineSchema(schema) {
    // filtering
    schema.filter('q', '=')
    for (let i = 0; i < serviceattributes.length; i++) {
      const field = serviceattributes[i];
      schema.filter(field, operators, { field: `${TbService.tableName}.${field}` })
      schema.sort(field, { field: `${TbService.tableName}.${field}` })
    }

    for (let i = 0; i < groupattributes.length; i++) {
      const field = groupattributes[i];
      schema.filter(field, operators, { field: `${TbServiceGroup.tableName}.${field}` })
      schema.sort(field, { field: `${TbServiceGroup.tableName}.${field}` })
    }

    for (let i = 0; i < typeattributes.length; i++) {
      const field = typeattributes[i];
      schema.filter(field, operators, { field: `${TbCounterService.tableName}.${field}` })
      schema.sort(field, { field: `${TbCounterService.tableName}.${field}` })
    }

    for (let i = 0; i < servicetypeattributes.length; i++) {
      const field = servicetypeattributes[i];
      schema.filter(field, operators, { field: `${TbCounterServiceType.tableName}.${field}` })
      schema.sort(field, { field: `${TbCounterServiceType.tableName}.${field}` })
    }
    schema.page(!!this.query.page)
  }

  'filter:q[=]'(builder, { value }) {
    value = `%${value}%`
    return builder.where(function () {
      for (let i = 0; i < serviceattributes.length; i++) {
        const field = serviceattributes[i];
        this.orWhere(`${TbService.tableName}.${field}`, 'like', value)
      }
      for (let i = 0; i < groupattributes.length; i++) {
        const field = groupattributes[i];
        this.orWhere(`${TbServiceGroup.tableName}.${field}`, 'like', value)
      }
      for (let i = 0; i < typeattributes.length; i++) {
        const field = typeattributes[i];
        this.orWhere(`${TbCounterService.tableName}.${field}`, 'like', value)
      }
      for (let i = 0; i < servicetypeattributes.length; i++) {
        const field = servicetypeattributes[i];
        this.orWhere(`${TbCounterServiceType.tableName}.${field}`, 'like', value)
      }
    })
  }
}

module.exports = Querier