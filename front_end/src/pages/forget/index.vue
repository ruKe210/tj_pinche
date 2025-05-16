<template>
  <view class="forget-container">
    <view class="form-container">
      <!-- 手机号输入框 -->
      <view class="input-group">
        <text class="label">手机号</text>
        <input 
          type="number" 
          placeholder="请输入注册手机号" 
          v-model="phone"
          maxlength="11"
        />
      </view>


      <!-- 新密码输入框 -->
      <view class="input-group">
        <text class="label">新密码</text>
        <input 
          type="password" 
          placeholder="请输入新密码" 
          v-model="password"
        />
      </view>

      <!-- 确认密码输入框 -->
      <view class="input-group">
        <text class="label">确认密码</text>
        <input 
          type="password" 
          placeholder="请再次输入新密码" 
          v-model="confirmPassword"
        />
      </view>

      <!-- 提交按钮 -->
      <button 
        class="submit-btn" 
        @click="handleSubmit" 
        :disabled="!isFormValid"
      >
        重置密码
      </button>

      <!-- 返回登录链接 -->
      <view class="links">
        <text class="link" @click="navigateToLogin">返回登录</text>
      </view>
    </view>
  </view>
</template>


<script>
import { resetPassword } from '@/api/api'

export default {
  data () {
    return {
      phone: '',
      password: '',
      confirmPassword: ''
    }
  },

  computed: {
    isPhoneValid () {
      return /^1[3-9]\d{9}$/.test(this.phone)
    },

    isFormValid () {
      return (
        this.isPhoneValid &&
        this.password.length >= 6 &&
        this.password === this.confirmPassword
      )
    }
  },

  methods: {

    async handleSubmit () {
      if (!this.isFormValid) return

      try {
        wx.showLoading({
          title: '提交中...',
          mask: true
        })

        const res = await resetPassword({
          phone: this.phone,
          verifyCode: this.verifyCode,
          password: this.password
        })

        if (res.success) {
          wx.showToast({
            title: '密码重置成功',
            icon: 'success',
            duration: 1500
          })
          setTimeout(() => {
            wx.redirectTo({
              url: '/pages/login/main'
            })
          }, 1500)
        } else {
          throw new Error(res.message || '重置失败')
        }
      } catch (error) {
        console.error('重置密码失败:', error)
        wx.showToast({
          title: error.message || '重置失败',
          icon: 'none'
        })
      } finally {
        wx.hideLoading()
      }
    },

    navigateToLogin () {
      wx.redirectTo({
        url: '/pages/login/main'
      })
    }
  }
}
</script>

<style>
.forget-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40rpx;
  min-height: 100vh;
  background-color: #f8f8f8;
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

.verify-btn {
  margin-left: 20rpx;
  padding: 0 20rpx;
  height: 60rpx;
  line-height: 60rpx;
  font-size: 24rpx;
  color: #07C160;
  background: none;
  border: 1px solid #07C160;
  border-radius: 30rpx;
}

.verify-btn[disabled] {
  color: #999;
  border-color: #ddd;
}

.submit-btn {
  margin-top: 60rpx;
  height: 88rpx;
  line-height: 88rpx;
  background-color: #07C160;
  color: #fff;
  text-align: center;
  font-size: 32rpx;
  border-radius: 44rpx;
}

.submit-btn[disabled] {
  background-color: #e0e0e0;
  color: #999;
}

.links {
  display: flex;
  justify-content: center;
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