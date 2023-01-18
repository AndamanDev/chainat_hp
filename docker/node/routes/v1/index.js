const express = require('express');
const router = express.Router();

const ServiceContoller = require('../../controllers/v1/service')
const ServiceGroupContoller = require('../../controllers/v1/service-group')
const CounterServiceContoller = require('../../controllers/v1/counter-service')
const DrugConfigController = require('../../controllers/v1/drug-config')

router.get(`/service-group/list`, ServiceGroupContoller.getServiceGroups)
router.get(`/service/list`, ServiceContoller.getServices)
router.get(`/counter-service/list`, CounterServiceContoller.getCounterServices)
router.get(`/drug-config/list`, DrugConfigController.getDrugsConfig)

module.exports = router;