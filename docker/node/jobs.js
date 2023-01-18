const Queue = require("bull")
const devMode = process.env.NODE_ENV === 'development'

let config = {
  host: process.env.REDIS_HOST,
  port: process.env.REDIS_PORT,
}
if (!devMode) {
  config = Object.assign(config, { password: process.env.REDIS_PASSWORD })
}

var messageQueue = new Queue("Notification", { redis: config })

module.exports = function (admin) {
  messageQueue.process(async function (job) {
    try {
      await admin.messaging().send(job.data)

      job.progress(100)

      return Promise.resolve(job.data)
    } catch (error) {
      return Promise.reject(error)
    }
  })

  messageQueue.on("completed", (job) => {
    console.log(`Job with id ${job.id} has been completed`)
  })
  messageQueue.on("error", function (error) {
    console.log(`Job error: ${error}`)
    // An error o`Job with id ${job.id} has been completed`ccured.
  })
  messageQueue.on("waiting", function (jobId) {
    // A Job is waiting to be processed as soon as a worker is idling.
    console.log(`Job is waiting ${jobId}`)
  })

  messageQueue.on("active", function (job, jobPromise) {
    // A job has started. You can use `jobPromise.cancel()`` to abort it.
    console.log(`Job is active ${job.id}`)
  })

  messageQueue.on("stalled", function (job) {
    // A job has been marked as stalled. This is useful for debugging job
    // workers that crash or pause the event loop.
    console.log(`Job is stalled ${job.id}`)
  })

  messageQueue.on("progress", function (job, progress) {
    // A job's progress was updated!
    console.log(`Job ${job.id} is ${progress * 100}% ready!`)
  })

  messageQueue.on("completed", function (job, result) {
    console.log(`Job ${job.id} completed!`)
  })

  messageQueue.on("failed", function (job, err) {
    // A job failed with reason `err`!
    console.log(`Job is failed ${err}`)
  })

  // ...whereas global events only pass the job ID:
  messageQueue.on("global:progress", function (jobId, progress) {
    console.log(`Job ${jobId} is ${progress * 100}% ready!`)
  })

  messageQueue.on("global:completed", function (jobId, result) {
    console.log(`Job ${jobId} completed! Result: ${result}`)
    messageQueue.getJob(jobId).then(function (job) {
      job.remove()
    })
  })

  return messageQueue
}
