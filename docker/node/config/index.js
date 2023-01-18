const moment = require("moment")
moment.locale("th")

module.exports = {
  pinoLogger: {
    timestamp: () => {
      return `,"time": "${moment().format()}"`
    },
  },
}