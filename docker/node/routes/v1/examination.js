const express = require('express');
const router = express.Router();
const controllers = require('../../controllers/examination')
const authenticate = require('../../middlewares/authenticate')

router.get(`/waiting-list`, authenticate, controllers.getQueueWaitingList)
router.get(`/calling-list`, authenticate, controllers.getQueueCallingList)
router.get(`/hold-list`, authenticate, controllers.getQueueHoldList)

module.exports = router;