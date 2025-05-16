<template>
  <view class="register-container">
    <view class="form-container">
      <view class="avatar-section">
        <button 
          class="avatar-wrapper"
          open-type="chooseAvatar" 
          @chooseavatar="onChooseAvatar"
        >
          <image 
            :src="avatarUrl || '/static/default-avatar.png'"
            class="avatar-image"
          />
        </button>
        <text class="avatar-tip">点击更换头像</text>
      </view>

      <view class="input-group">
        <text class="label">昵称</text>
        <input 
          type="nickname"
          placeholder="请输入昵称" 
          v-model="nickName"
          @blur="onNickNameChange"
        />
      </view>

      <view class="input-group">
        <text class="label">性别</text>
        <view class="gender-buttons">
          <button 
            class="gender-btn" 
            :class="{ active: gender === 1 }" 
            @click="selectGender(1)"
          >
            男
          </button>
          <button 
            class="gender-btn" 
            :class="{ active: gender === 2 }" 
            @click="selectGender(2)"
          >
            女
          </button>
        </view>
      </view>



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

      <view class="input-group">
        <text class="label">确认密码</text>
        <input 
          type="password" 
          placeholder="请再次输入密码" 
          v-model="confirmPassword"
        />
      </view>

      <button 
        class="register-btn" 
        @click="handleRegister" 
        :disabled="!isFormValid"
      >
        注册
      </button>

      <view class="links">
        <text class="link" @click="navigateToLogin">返回登录</text>
      </view>
    </view>
  </view>
</template>

<script>
import { register, uploadAvatar } from '@/api/api'

export default {
  data () {
    return {
      nickName: '',
      gender: 0,
      phone: '',
      password: '',
      confirmPassword: '',
      avatarUrl: ''
    }
  },

  computed: {
    isFormValid () {
      const phonePattern = /^1[3-9]\d{9}$/
      return (
        this.nickName &&
        this.avatarUrl &&
        this.gender !== 0 &&
        phonePattern.test(this.phone) &&
        this.password.length >= 6 &&
        this.password === this.confirmPassword
      )
    }
  },

  methods: {
    async onChooseAvatar (e) {
      if (e && e.mp && e.mp.detail) {
        const tempFilePath = e.mp.detail.avatarUrl
        try {
          wx.showLoading({
            title: '上传中...',
            mask: true
          })
          const fileData = await this.fileToBase64(tempFilePath)
          const uploadRes = await uploadAvatar(fileData)
          if (uploadRes.success) {
            this.avatarUrl = uploadRes.url
            wx.showToast({
              title: '上传成功',
              icon: 'success'
            })
          } else {
            throw new Error(uploadRes.message || '上传失败')
          }
        } catch (error) {
          console.error('上传头像失败:', error)
          wx.showToast({
            title: error.message || '上传失败',
            icon: 'none'
          })
        } finally {
          wx.hideLoading()
        }
      }
    },

    fileToBase64 (filePath) {
      return new Promise((resolve, reject) => {
        wx.getFileSystemManager().readFile({
          filePath: filePath,
          encoding: 'base64',
          success: res => {
            resolve(res.data)
          },
          fail: err => {
            reject(err)
          }
        })
      })
    },

    onNickNameChange (e) {
      if (e && e.mp && e.mp.detail) {
        this.nickName = e.mp.detail.value
      }
    },

    selectGender (value) {
      this.gender = value
    },

    handleRegister () {
      if (!this.isFormValid) return

      wx.showLoading({
        title: '注册中...',
        mask: true
      })

      const userInfo = {
        nickName: this.nickName,
        gender: this.gender,
        phone: this.phone,
        password: this.password,
        avatarUrl: this.avatarUrl
      }

      register(userInfo).then(() => {
        wx.hideLoading()
        wx.showToast({
          title: '注册成功',
          icon: 'success',
          duration: 1500
        })
        setTimeout(() => {
          wx.redirectTo({
            url: '/pages/login/main'
          })
        }, 1500)
      }).catch(error => {
        wx.hideLoading()
        wx.showToast({
          title: error.message || '注册失败',
          icon: 'none',
          duration: 1500
        })
        console.error('注册失败:', error)
      })
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
.register-container {
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

.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 40rpx 0;
}

.avatar-wrapper {
  padding: 0;
  width: 160rpx;
  height: 160rpx;
  border-radius: 80rpx;
  background: none;
}

.avatar-wrapper::after {
  border: none;
}

.avatar-image {
  width: 160rpx;
  height: 160rpx;
  border-radius: 80rpx;
}

.avatar-tip {
  margin-top: 20rpx;
  font-size: 24rpx;
  color: #666;
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

.gender-buttons {
  display: flex;
  gap: 20rpx;
  flex: 1;
}

.gender-btn {
  flex: 1;
  height: 60rpx;
  line-height: 60rpx;
  text-align: center;
  font-size: 28rpx;
  color: #666;
  background-color: #f0f0f0;
  border-radius: 30rpx;
  border: 1rpx solid #ddd;
}

.gender-btn.active {
  color: #fff;
  background-color: #07C160;
  border-color: #07C160;
}

.register-btn {
  margin-top: 60rpx;
  height: 88rpx;
  line-height: 88rpx;
  background-color: #07C160;
  color: #fff;
  text-align: center;
  font-size: 32rpx;
  border-radius: 44rpx;
}

.register-btn[disabled] {
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