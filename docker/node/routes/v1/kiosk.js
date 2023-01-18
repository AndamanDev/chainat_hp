const express = require('express');
const router = express.Router();
const validate = require('../../middlewares/validation')
const validators = require('../../validations/kiosk.validator')
const controllers = require('../../controllers/kiosk')

router.post(`/create-queue`, validate(validators.createQueue), controllers.postCreateQueue)
router.get(`/queue-list`, controllers.getQueueList)

module.exports = router;