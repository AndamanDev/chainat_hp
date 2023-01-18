const { TbQueue, TbService, TbQueueTrans, TbServiceStatus, TbCounterService, TbCaller } = require('../index')
const BaseQuerier = require('./base-querier')
const operators = require('./operators')
const attributes = Object.keys(TbQueue.attributeLabels())
const serviceAttributes = Object.keys(TbService.attributeLabels())
const transAttributes = Object.keys(TbQueueTrans.attributeLabels())
const serviceStatusAttributes = Object.keys(TbServiceStatus.attributeLabels())
const counterAttributes = Object.keys(TbCounterService.attributeLabels())
const callerAttributes = Object.keys(TbCaller.attributeLabels())

class Querier extends BaseQuerier {
  defineSchema(schema) {
    // filtering
    schema.filter('q', '=')
    for (let i = 0; i < attributes.length; i++) {
      const field = attributes[i];
      schema.filter(field, operators, { field: `${TbQueue.tableName}.${field}` })
      schema.sort(field, { field: `${TbQueue.tableName}.${field}` })
    }
    for (let i = 0; i < serviceAttributes.length; i++) {
      const field = serviceAttributes[i];
      schema.filter(field, operators, { field: `${TbService.tableName}.${field}` })
      schema.sort(field, { field: `${TbService.tableName}.${field}` })
    }
    // for (let i = 0; i < transAttributes.length; i++) {
    //   const field = transAttributes[i];
    //   schema.filter(field, operators, { field: `${TbQueueTrans.tableName}.${field}` })
    //   schema.sort(field, { field: `${TbQueueTrans.tableName}.${field}` })
    // }
    // for (let i = 0; i < serviceStatusAttributes.length; i++) {
    //   const field = serviceStatusAttributes[i];
    //   schema.filter(field, operators, { field: `${TbServiceStatus.tableName}.${field}` })
    //   schema.sort(field, { field: `${TbServiceStatus.tableName}.${field}` })
    // }
    for (let i = 0; i < counterAttributes.length; i++) {
      const field = counterAttributes[i];
      schema.filter(field, operators, { field: `${TbCounterService.tableName}.${field}` })
      schema.sort(field, { field: `${TbCounterService.tableName}.${field}` })
    }
    for (let i = 0; i < callerAttributes.length; i++) {
      const field = callerAttributes[i];
      schema.filter(field, operators, { field: `${TbCaller.tableName}.${field}` })
      schema.sort(field, { field: `${TbCaller.tableName}.${field}` })
    }
    schema.page(!!this.query.page)
  }

  'filter:q[=]'(builder, { value }) {
    value = `%${value}%`
    return builder.where(function () {
      for (let i = 0; i < attributes.length; i++) {
        const field = attributes[i];
        this.orWhere(`${TbQueue.tableName}.${field}`, 'like', value)
      }
      for (let i = 0; i < serviceAttributes.length; i++) {
        const field = serviceAttributes[i];
        this.orWhere(`${TbService.tableName}.${field}`, 'like', value)
      }
      // for (let i = 0; i < transAttributes.length; i++) {
      //   const field = transAttributes[i];
      //   this.orWhere(`${TbQueueTrans.tableName}.${field}`, 'like', value)
      // }
      // for (let i = 0; i < serviceStatusAttributes.length; i++) {
      //   const field = serviceStatusAttributes[i];
      //   this.orWhere(`${TbServiceStatus.tableName}.${field}`, 'like', value)
      // }
      for (let i = 0; i < counterAttributes.length; i++) {
        const field = counterAttributes[i];
        this.orWhere(`${TbCounterService.tableName}.${field}`, 'like', value)
      }
      for (let i = 0; i < callerAttributes.length; i++) {
        const field = callerAttributes[i];
        this.orWhere(`${TbCaller.tableName}.${field}`, 'like', value)
      }
    })
  }
}

module.exports = Querier