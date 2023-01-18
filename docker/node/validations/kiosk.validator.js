const yup = require('yup')

module.exports = {
  createQueue: {
    patient_info: yup.object().nullable().required('invalid patient_info.'),
    servicegroupid: yup.number().nullable().integer().required('invalid servicegroupid.'),
    serviceid: yup.number().nullable().integer().required('invalid serviceid.'),
    created_from: yup.number().nullable().integer().required('invalid created_from.'),
  }
}