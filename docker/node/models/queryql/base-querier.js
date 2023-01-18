const QueryQL = require('@truepic/queryql')
const _ = require('lodash')

class BaseQuerier extends QueryQL {
  get pageDefaults() {
    return {
      size: 10,
      number: 1,
    }
  }

  async run() {
    super.run()

    let builder = this.builder
    const clone = builder.clone()
    clone._single = _.omit(clone._single, ['limit', 'offset'])
    clone._statements = clone._statements.filter((item) => item.grouping !== 'group')
    // check get all rows
    if (_.get(this.query, 'page.size', 10) === -1) {
      builder = _.omit(builder, ['limit', 'offset'])
    }

    const page = _.get(this.query, 'page')
    const currentPage = parseInt(_.get(page, 'number', 1))
    let perPage = parseInt(_.get(page, 'size', this.pageDefaults.size))

    let offset = (currentPage - 1) * perPage
    const total = await clone.count('*', { as: 'count' }).first()
    let count = 0
    if (total) {
      count = _.get(total, 'count', 0)
    }
    const totalCount = parseInt(count, 10)
    if(!_.get(this.query, 'page')) {
      perPage = totalCount;
      offset = (currentPage - 1) * perPage
    }
    const pageCount = Math.ceil(totalCount / perPage)
    const rows = await builder

    return {
      total: totalCount,
      perPage: perPage,
      offset: offset,
      to: !rows.length ? 0 : offset + (rows.length - 1),
      lastPage: Math.ceil(count / perPage),
      currentPage: currentPage,
      pageCount: pageCount,
      from: offset,
      data: rows,
    }
  }
}

module.exports = BaseQuerier
