const express = require('express');
const router = express.Router();
const controllers = require('../../controllers/v1/setting')
const authenticate = require('../../middlewares/authenticate')

router.get(`/service-groups`, authenticate, controllers.getServiceGroups)
router.get(`/sounds`, authenticate, controllers.getSounds)
router.get(`/displays`, authenticate, controllers.getDisplays)
router.get(`/counters`, authenticate, controllers.getCounters)
router.get(`/tickets`, authenticate, controllers.getTickets)
router.get(`/service-profiles`, authenticate, controllers.getServiceProfiles)
router.get(`/sound-stations`, authenticate, controllers.getSoundStations)
router.get(`/calling-configs`, authenticate, controllers.getCallingConfigs)

module.exports = router;