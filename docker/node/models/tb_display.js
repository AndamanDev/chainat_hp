const db = require('../config/db').mysql

class DisplayModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_display_config'
  }

  static attributeLabels() {
    return {
      display_ids: 'ไอดี',
      display_name: '',
      counterservice_id: '',
      service_id: '',
      title_left: '',
      title_right: '',
      title_latest: '',
      table_title_left: '',
      table_title_right: '',
      title_latest_right: '',
      display_limit: '',
      hold_label: '',
      header_color: '',
      column_color: '',
      background_color: '',
      font_color: '',
      border_color: '',
      title_color: '',
      display_status: '',
      text_marquee: '',
      title_left_color: '',
      title_right_color: '',
      title_latest_color: '',
      table_title_left_color: '',
      table_title_right_color: '',
      title_latest_right_color: '',
      font_cell_display_color: '',
      cell_hold_bg_color: '',
      header_latest_color: '',
      cell_latest_color: '',
      font_cell_latest_color: '',
      border_cell_latest_color: '',
      hold_bg_color: '',
      hold_font_color: '',
      hold_border_color: '',
      font_marquee_color: '',
      lab_display: '',
      sound_station_id: '',
      pt_name: '',
      pt_pic: ''
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('display_ids', id).first()
  }

  static deleteById(id) {
    return this.delete({ display_ids: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

DisplayModel.tableName = 'tb_display_config'
DisplayModel.db = db

module.exports = DisplayModel