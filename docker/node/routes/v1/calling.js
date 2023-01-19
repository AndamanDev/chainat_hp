const express = require('express');
const router = express.Router();
const controllers = require('../../controllers/calling')
const authenticate = require('../../middlewares/authenticate')

router.get(`/waiting-list`, authenticate, controllers.getQueueWaitingList)
router.get(`/calling-list`, authenticate, controllers.getQueueCallingList)
router.get(`/hold-list`, authenticate, controllers.getQueueHoldList)
router.post(`/call`, authenticate, controllers.postCall)
router.post(`/recall`, authenticate, controllers.postRecall)
router.post(`/hold`, authenticate, controllers.postHold)
router.post(`/end`, authenticate, controllers.postEnd)
router.post(`/waiting-doctor`, authenticate, controllers.postWaittingDoctor)
router.post(`/waiting-pharmacy`, authenticate, controllers.postWaitingPharmacy)
router.get(`/next-queue`, controllers.getNextQueue)
router.post(`/calling-queue`, controllers.postCallingQueue)
router.post(`/hold-queue`, controllers.postHoldQueue)
router.post(`/end-queue`, controllers.postEndQueue)
router.post(`/waiting-doctor-queue`, controllers.postWaitingDoctorQueue)
router.post(`/waiting-pharmacy-queue`, controllers.postWaitingPharmacyQueue)

module.exports = router;