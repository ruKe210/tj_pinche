<script>
export default {
  methods: {
    wxLogin () {
      const self = this
      wx.login({
        success (loginRes) {
          console.log('登录成功！' + loginRes.code)
          if (loginRes.code) {
            wx.getSetting({
              success (settRes) {
                if (settRes.authSetting['scope.userInfo']) {
                  console.log('登录成功！')
                  self.getWxUserInfo(loginRes.code)
                } else {
                  self.$store.dispatch('AuthUser', loginRes.code).then(() => {
                  }).catch(error => {
                    console.log(error)
                  })
                }
              }
            })
          } else {
            console.log('登录失败！' + loginRes.errMsg)
          }
        },
        fail (error) {
          console.log('登录失败' + error)
        }
      })
    },
    getWxUserInfo (code) {
      const self = this
      wx.getUserInfo({
        success (info) {
          console.log('获取用户信息成功！' + info)
          self.$store.dispatch('GetUser', { code, info }).then(() => {
          }).catch(error => {
            console.log(error)
          })
        }
      })
    },
    loadConfig () {
      this.$store.dispatch('GetConfig').then(() => {
      }).catch(error => {
        console.log(error)
      })
    }
  },
  onLaunch (options) {
    // this.loadConfig()
    this.wxLogin()
  },
  onError (msg) {
    console.warn('全局异常拦截->')
    console.warn(msg)
  }
}
</script>

<style>

</style>
