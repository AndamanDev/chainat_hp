const db = require('../config/db').mysql

class SettingModel {
  constructor() {
    this.db = db
  }

  static getServiceGroups() {
    return this.db
      .select([
        db.raw(`tb_servicegroup.servicegroupid AS DT_RowId`),
        'tb_servicegroup.*',
        'tb_service.*',
        'tb_service_type.*'
      ])
      .from('tb_servicegroup')
      .leftJoin('tb_service', 'tb_service.service_groupid', 'tb_servicegroup.servicegroupid')
      .leftJoin('tb_service_type', 'tb_service.service_type_id', 'tb_service_type.service_type_id')
      .orderBy('tb_servicegroup.servicegroupid', 'asc')
  }

  static getCounters() {
    return this.db
      .select([
        'tb_counterservice_type.*',
        db.raw(`tb_counterservice_type.counterservice_type AS counterservice_type_name`),
        'tb_counterservice.*',
        'tb_servicegroup.*',
        'tb_service.*',
        db.raw(`(select tb_sound.sound_th FROM tb_sound WHERE tb_sound.sound_id = tb_counterservice.sound_id LIMIT 1) as sound_name`),
        db.raw(`(select tb_sound.sound_th FROM tb_sound WHERE tb_sound.sound_id = tb_counterservice.sound_service_id LIMIT 1) as sound_service_name`),
      ])
      .from('tb_counterservice')
      .leftJoin('tb_counterservice_type', 'tb_counterservice_type.counterservice_typeid', 'tb_counterservice.counterservice_type')
      .leftJoin('tb_service', 'tb_service.serviceid', 'tb_counterservice.serviceid')
      .leftJoin('tb_servicegroup', 'tb_servicegroup.servicegroupid', 'tb_service.service_groupid')
      // .groupBy('tb_counterservice.counterserviceid')
      .orderBy('tb_counterservice.counterservice_type', 'asc')
      .orderBy('tb_counterservice.service_order', 'asc')
      .orderBy('tb_counterservice.counterservice_callnumber', 'asc')
  }

  static getServiceProfiles() {
    return this.db
      .select([
        'tb_service_profile.*',
        'tb_counterservice_type.*'
      ])
      .from('tb_service_profile')
      .leftJoin('tb_counterservice_type', 'tb_counterservice_type.counterservice_typeid', 'tb_service_profile.counterservice_typeid')
  }
}

SettingModel.db = db

module.exports = SettingModel