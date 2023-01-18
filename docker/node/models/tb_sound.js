const db = require('../config/db').mysql

class SoundModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_sound'
  }

  static attributeLabels() {
    return {
      sound_id: 'ไอดี',
      sound_name: '',
      sound_path_name: '',
      sound_th: '',
      sound_type: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('sound_id', id).first()
  }

  static findOne = (condition) => {
    return this.find().where(condition).first()
  }

  static deleteById(id) {
    return this.delete({ sound_id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

SoundModel.tableName = 'tb_sound'
SoundModel.db = db

module.exports = SoundModel