<template>
  <view id="my">
    <view class="header">
      <view class="user">

        <view class="avatar">
          <img :src="userInfo.avatarUrl || 'http://localhost:90/default-avatar.png'"/>
        </view>
        <view class="nickname">{{ userInfo.nickName }}</view>

      </view>

      <view class="setting">
        <button class="wx-setting" open-type="openSetting">
          <text class="fa fa-cog fa-lg gray-icon"/>
        </button>
      </view>
    </view>

    <view class="content">
      <view class="record">
        <view class="left" hover-class="tab-hover"  @click="collect()">
          <view class="warp">
            <view class="num">0</view>
            <view class="desc">我收藏的</view>
          </view>
        </view>
        <view class="right" hover-class="tab-hover" @click="release()">
          <view class="warp">
            <view class="num">{{ releaseCount }}</view>
            <view class="desc">我发布的</view>
          </view>
        </view>
      </view>

      <view class="bar" hover-class="tab-hover">
        <button class="share-btn" open-type="share">
          <view class="icon">
            <text class="fa fa-share-alt fa-sm gray-icon"/>
          </view>
          <text class="text">推荐给朋友</text>
        </button>
      </view>
      <view class="bar" hover-class="tab-hover">
        <button class="share-btn" open-type="contact">
          <view class="icon">
            <text class="fa fa-comment-o fa-sm gray-icon"/>
          </view>
          <text class="text">联系开发者</text>
        </button>
      </view>
      <view class="bar" v-if="admin" @click="manger()" hover-class="tab-hover">
        <view class="icon">
          <text class="fa fa-toggle-off fa-lg gray-icon"/>
        </view>
        <text class="text">管理员入口</text>
      </view>

    </view>

    <!-- <view class="easter-egg" @click="service()">
      <view class="title">
        <view class="left">—</view>
        <view class="center">Cheng 出品</view>
        <view class="right">—</view>
      </view>
      <view class="text">{{ weChat }}</view>
    </view> -->

  </view>

</template>

<script>
  import { getReleaseCount } from '@/api/api'
  import { errorToast } from '@/utils/index'
  import { mapGetters } from 'vuex'
  export default {
    data () {
      return {
        userInfo: {},
        releaseCount: 0
        // weChat: 'chengzhx76'
      }
    },
    methods: {
      collect () {
        wx.showModal({
          content: '功能正在开发中~',
          showCancel: false,
          confirmText: '敬请期待',
          success (res) {
            if (res.confirm) {
            }
          }
        })
      },
      release () {
        const url = '../release/main'
        wx.navigateTo({ url })
      },
      manger () {
        const url = '../admin/main'
        wx.navigateTo({ url })
      },
      getReleaseCount (phone) {
        getReleaseCount(phone).then(res => {
          console.log(res)
          if (res.meta.code === 2000) {
            this.releaseCount = res.data
          }
        })
      },
      service () {
        const that = this
        wx.setClipboardData({
          data: that.weChat,
          success (res) {
            errorToast('微信号已复制')
          }
        })
      }
    },
    onShow () {
      const userToken = wx.getStorageSync('user_token')
      console.log('当前用户token:', userToken)
      if (!userToken) {
        // 如果未登录，跳转到登录页面
        // wx.reLaunch({
        wx.navigateTo({
          url: '/pages/login/main'
        })
      } else {
        // 获取并打印用户信息
        const userInfo = wx.getStorageSync('user_info')
        this.userInfo = userInfo
        console.log('当前用户信息:', userInfo)
      }
      this.getReleaseCount(this.userInfo.phone)
    },
    onLoad () {
      // Object.assign(this.$data, this.$options.data())
      // this.getReleaseCount()
      // const userInfo = wx.getStorageSync('user_info')
      // this.userInfo = userInfo
      // console.log('当前用户信息:', userInfo)
    },
    computed: {
      ...mapGetters([
        'avatar',
        'nickName',
        'admin',
        'shareText',
        'shareImg'
      ])
    },
    onShareAppMessage (res) {
      return {
        title: this.shareText.index,
        path: 'pages/index/main',
        imageUrl: this.shareImg.index
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  @import "@/styles/mixin.scss";
  @import "@/styles/variables.scss";
  #my {
    @include height-width-100;
  }


  .header {
    @include height-rpx-width-100(260);
    @include justify-space-between;
    background: $dark-yellow;
    .user {
      @include height-100-width-percent(50%);
      display: flex;
      padding-left: 30rpx;
      .avatar {
        @include height-100-width-rpx(150);
        @include align-center;
        img {
          @include height-width(150, 150);
          @include border-radius(75);
        }
      }
      .nickname {
        font-size: 56rpx;
        color: #222;
        font-weight: bold;
        letter-spacing: 2rpx;
        text-shadow: 2rpx 2rpx 8rpx #f5e6b2;
        margin-left: 18rpx;
        /* 居中基础上可微调上下位置 */
        margin-top: var(--nickname-offset, 90rpx); // 默认0，可在style里覆盖
        line-height: 1; // 避免撑高
      }
    }
    .setting {
      @include height-width-text-center(80, 120);
      color: $unimpColor;
      .wx-setting {
        @include height-width-text-center(80, 120);
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        text-align: center;
        text-decoration: none;
        line-height: 80rpx;
        border-radius: 0;
        -webkit-tap-highlight-color: transparent;
        overflow: hidden;
        background-color: $dark-yellow;
      }
    }
  }
  .content {
    width: 100%;
    margin-top: 30rpx;
    margin-bottom: 100rpx;
    .record {
      @include height-rpx-width-100(200);
      @include justify-start-align-center;
      background: $white;
      .left, .right {
        @include height-rpx-width-percent(200, 50%);
        .warp {
          @include height-rpx-width-percent(140, 100%);
          margin-top: 30rpx;
          .num {
            @include height-width-percent-text-center(80);
            font-size: 50rpx;
            color: $unimpColor;
          }
          .desc {
            @include height-width-percent-text-center(60);
            font-size: 36rpx;
            color: $tipColor;
          }
        }
      }
      .left {
        .warp {
          @include border-right-width(1);
        }
      }
    }
    .bar {
      @include height-rpx-width-100(110);
      @include justify-start-align-center;
      margin-top: 30rpx;
      position: relative;
      background: $white;
      .share-btn {
        @include height-rpx-width-100(110);
        @include justify-start-align-center;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        text-align: center;
        text-decoration: none;
        line-height: 110rpx;
        border-radius: 0;
        -webkit-tap-highlight-color: transparent;
        overflow: hidden;
        background-color: $white;
      }
      .icon {
        width: 60rpx;
        margin-left: 20rpx;
      }
      .text {
        font-size: 18px;
        color: $titleColor;
      }
      &:after {
        @include arrow(18, 25, 45);
      }
    }
  }

  .easter-egg {
    @include height-rpx-width-100(150);
    @include column-align-center;
    .title {
      @include height-rpx-width-100(80);
      @include justify-align-center;
      color: $unimpColor;
      .left, .right {
        @include height-line(80);
        font-size: 28rpx;
      }
      .center {
        @include height-line(80);
        margin: 0 18rpx;
        font-size: 44rpx;
        font-weight: bold;
      }
    }
    .text {
      @include height-line(40);
      font-size: 32rpx;
      color: $tipColor;
    }
  }

</style>
