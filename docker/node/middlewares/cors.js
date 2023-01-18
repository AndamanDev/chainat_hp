module.exports = (req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*')
  // res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization')
  res.header(
    'Access-Control-Allow-Headers',
    'Authorization, Accept, Origin, DNT, X-CustomHeader, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Content-Range, Range, X-Firebase-Token'
  )
  if (req.method === 'OPTIONS') {
    res.header('Access-Control-Allow-Methods', 'PUT, POST, PATCH, DELETE, GET') // to give access to all the methods provided
    return res.status(200).json({})
  }
  next()
}
