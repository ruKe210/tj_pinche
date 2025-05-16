import { login } from '@/api/api'

const user = {
  state: {
    uid: '',
    avatar: '',
    mobileNo: '',
    gender: 0,
    nickName: '',
    token: '',
    isLogin: false // 新增登录状态标识
  },

  mutations: {
    SET_USER_INFO: (state, userInfo) => {
      state.uid = userInfo.userId
      state.avatar = userInfo.avatarUrl
      state.nickName = userInfo.nickName
      state.gender = userInfo.gender
      state.mobileNo = userInfo.phone
      state.token = userInfo.token
      state.isLogin = true
    },
    CLEAR_USER_INFO: (state) => {
      state.uid = ''
      state.avatar = ''
      state.nickName = ''
      state.gender = 0
      state.mobileNo = ''
      state.token = ''
      state.isLogin = false
    }
  },

  actions: {
    // 登录
    Login ({ commit }, loginData) {
      return new Promise((resolve, reject) => {
        login(loginData).then(res => {
          if (res.success) {
            // 存储用户信息
            wx.setStorageSync('user_token', res.data.token)
            commit('SET_USER_INFO', res.data)
            resolve(res)
          } else {
            reject(res)
          }
        }).catch(error => reject(error))
      })
    },

    // 获取用户信息
    GetUserInfo ({ commit }) {
      return new Promise((resolve, reject) => {
        const userInfo = wx.getStorageSync('user_info')
        if (userInfo) {
          commit('SET_USER_INFO', userInfo)
          resolve(userInfo)
        }
      })
    },

    // 退出登录
    Logout ({ commit }) {
      return new Promise(resolve => {
        wx.removeStorageSync('user_token')
        wx.removeStorageSync('user_info')
        commit('CLEAR_USER_INFO')
        resolve()
      })
    }
  }
}

export default user
