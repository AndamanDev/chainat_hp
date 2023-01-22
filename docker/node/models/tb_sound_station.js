const db = require('../config/db').mysql

class SoundStationModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_sound_station'
  }

  static attributeLabels() {
    return {
      sound_station_id: 'ไอดี',
      sound_station_name: '',
      counterserviceid: '',
      sound_station_status: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('sound_station_id', id).first()
  }

  static findOne = (condition) => {
    return this.find().where(condition).first()
  }

  static deleteById(id) {
    return this.delete({ sound_station_id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

SoundStationModel.tableName = 'tb_sound_station'
SoundStationModel.db = db

module.exports = SoundStationModel