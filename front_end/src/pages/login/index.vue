<template>
  <view class="login-container">
    <view class="logo-container">
      <!-- <image src="/static/logo.png" mode="aspectFit" class="logo-image"></image> -->
      <text class="app-name">同济拼车</text>
    </view>
    
    <view class="form-container">
      <view class="input-group">
        <text class="label">手机号</text>
        <input 
          type="number" 
          placeholder="请输入手机号" 
          v-model="phone"
          maxlength="11"
        />
      </view>
      
      <view class="input-group">
        <text class="label">密码</text>
        <input 
          type="password" 
          placeholder="请输入密码" 
          v-model="password"
        />
      </view>
      
      <button 
        class="login-btn" 
        @click="handleLogin" 
        :disabled="!isFormValid"
      >
        登录
      </button>
      
      <view class="links">
        <text class="link" @click="navigateToRegister">注册账号</text>
        <text class="link" @click="navigateToForgot">忘记密码</text>
      </view>
    </view>
  </view>
</template>

<script>
import { login } from '@/api/api'

export default {
  data () {
    return {
      phone: '',
      password: ''
    }
  },

  computed: {
    isFormValid () {
      const phonePattern = /^1[3-9]\d{9}$/
      return phonePattern.test(this.phone) && this.password.length >= 6
    }
  },

  methods: {
    async handleLogin () {
      if (!this.isFormValid) return

      wx.showLoading({
        title: '登录中...',
        mask: true
      })
      try {
        const loginData = {
          phone: this.phone,
          password: this.password
        }
        const res = await login(loginData)
        if (res.success) {
          // 保存用户信息和token
          wx.setStorageSync('user_token', res.data.token)
          wx.setStorageSync('user_info', {
            userId: res.data.userId,
            avatarUrl: res.data.avatarUrl,
            nickName: res.data.nickName,
            gender: res.data.gender,
            phone: res.data.phone,
            token: res.data.token,
            loginTime: new Date().getTime()
          })

          wx.showToast({
            title: '登录成功',
            icon: 'success',
            duration: 1500
          })

          // 跳转到首页
          setTimeout(() => {
            wx.reLaunch({
              url: '../index/main'
            })
          }, 1500)
        } else {
          throw new Error(res.message || '登录失败')
        }
      } catch (error) {
        console.error('登录失败:', error)
        wx.showToast({
          title: error.message || '登录失败',
          icon: 'none',
          duration: 1500
        })
      } finally {
        wx.hideLoading()
      }
    },

    navigateToRegister () {
      wx.navigateTo({
        url: '/pages/register/main'
      })
    },

    navigateToForgot () {
      wx.navigateTo({
        url: '/pages/forget/main'
      })
    }
  }
}
</script>

<style>
.login-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40rpx;
  height: 100vh;
  background-color: #f8f8f8;
}

.logo-container {
  margin-top: 120rpx;
  margin-bottom: 80rpx;
  text-align: center;
}

.logo-image {
  width: 200rpx;
  height: 200rpx;
  border-radius: 20rpx;
}

.app-name {
  display: block;
  margin-top: 20rpx;
  font-size: 36rpx;
  font-weight: bold;
  color: #333;
}

.form-container {
  width: 100%;
  max-width: 600rpx;
}

.input-group {
  display: flex;
  align-items: center;
  padding: 20rpx 0;
  border-bottom: 1rpx solid #eee;
}

.label {
  width: 140rpx;
  font-size: 28rpx;
  color: #666;
}

input {
  flex: 1;
  height: 60rpx;
  font-size: 28rpx;
  color: #333;
}

.login-btn {
  margin-top: 60rpx;
  height: 88rpx;
  line-height: 88rpx;
  background-color: #07C160;
  color: #fff;
  text-align: center;
  font-size: 32rpx;
  border-radius: 44rpx;
}

.login-btn[disabled] {
  background-color: #e0e0e0;
  color: #999;
}

.links {
  display: flex;
  justify-content: space-between;
  margin-top: 30rpx;
}

.link {
  font-size: 24rpx;
  color: #666;
}

.link:active {
  opacity: 0.7;
}
</style>