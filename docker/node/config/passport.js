const passport = require('passport')
const { User } = require('../models')

passport.serializeUser(function (user, done) {
  done(null, user.id);
});

passport.deserializeUser(function (id, done) {
  User.findOneById(id)
    .then((user) => {
      done(null, user)
    })
    .catch((err) => done(err))
});