<template>
  <view id="index">

    <view class="main">
      <view class="section-header">
        <swiper class="swiper-main" :indicator-dots="swiper.indicatorDots" :autoplay="swiper.autoplay" :circular="swiper.circular"
                :vertical="swiper.vertical" :interval="swiper.interval" :duration="swiper.duration">
          <swiper-item class="swiper-item" v-for="(item, index) in swiper.imgs" :key="index">
            <image class="swiper-img" mode="scaleToFill" :src="item"/>
          </swiper-item>
        </swiper>
        <view class="nav-main">
          <view class="nav">
            <view v-for="(tab, index) in tabs"
                  :class="[tab.class, {active: tab.isActive}, 'btn']"
                  @click="tabsSwitch(tab.class, tab.type)"
                  :key="index"
                  hover-class="tab-hover">{{ tab.name }}</view>
          </view>
          <view class="my">
            <button class="user-info" hover-class="tab-hover" open-type="getUserInfo" @getuserinfo="showUserInfo">
              <view class="avatar-warp">
                <!-- <open-data class="avatar" type="userAvatarUrl"></open-data> -->
                 <image
                  class="avatar"
                  :src="userInfo.avatarUrl || 'http://localhost:90/default-avatar.png'"
                  mode="aspectFill"
                />
              </view>
              <view class="text">我的</view>
            </button>
          </view>
        </view>
      </view>
      <view class="search">
        <view class="search-main">

          <view class="service">
            <view class="distance">
              <view class="origin info">
                <view class="icon">
                  <text class="fa fa-car gray-icon"/>
                </view>
                <view class="input">
                  <input placeholder-class="placeholder-color" placeholder="哪出发" v-model="service.origin"/>
                </view>
              </view>
              <view class="destination info">
                <view class="icon">
                  <text class="fa fa-lg fa-map-marker gray-icon"/>
                </view>
                <view class="input">
                  <input placeholder-class="placeholder-color" placeholder="要去哪" v-model="service.dest"/>
                </view>
              </view>
            </view>
            <view class="change" hover-class="btn-hover" @click="exchange()">
              <text class="fa fa-1-6x fa-retweet gray-icon"/>
            </view>

          </view>
          <view class="search-btn">
            <button class="search-button" hover-class="btn-hover" @click="searchHandler">搜索顺路车程</button>
          </view>

        </view>
      </view>

    </view>
    <view class="list">
      <card-list :travels="list" @shareImgUrl="getShareImgUrl" @cancelActionSheet="cancelActionSheet"/>
      <view class="bottom-block" hover-class="btn-hover" @click="moreHandler">查看更多 ></view>
      <view class="block"></view>
    </view>

    <view class="add" v-if="switches.add" hover-class="btn-hover" @click="addHandler">
      <text class="icon">+</text>
    </view>

    <center-dialog ref="freezeDialog"/>
  </view>
</template>

<script>
  import { mapGetters } from 'vuex'
  import CardList from '@/components/CardList/index'
  import CenterDialog from '@/components/CenterDialog/index'
  import { list } from '@/api/api'
  import { formatTime, formatDateTime } from '@/utils/index'
  export default {
    data () {
      return {
        userInfo: {},
        service: {
          type: 0,
          origin: '',
          dest: ''
        },
        page: {
          pageNum: 1,
          totalNum: 0
        },
        list: [],
        tabs: [
          {
            name: '全部',
            class: 'all',
            type: 0,
            isActive: true
          },
          {
            name: '乘客',
            class: 'passenger',
            type: 1,
            isActive: false
          },
          {
            name: '车主',
            class: 'driver',
            type: 2,
            isActive: false
          }
        ],
        imageUrl: '',
        tid: ''
      }
    },
    components: {
      CardList,
      CenterDialog
    },
    methods: {
      tabsSwitch (clazz, type) {
        this.service = {
          type: 0,
          origin: '',
          dest: ''
        }
        this.service.type = type === 0 ? 0 : type === 1 ? 2 : 1
        this.getList()
        this.tabs.forEach(tab => {
          tab.isActive = clazz === tab.class
        })
      },
      exchange () {
        if (!!this.service.origin || !!this.service.dest) {
          let temp = this.service.origin
          this.service.origin = this.service.dest
          this.service.dest = temp
        }
      },
      searchHandler () {
        let type = this.service.type === 0 ? 0 : this.service.type === 2 ? 1 : 2
        const url = `../list/main?type=${type}&origin=${this.service.origin}&dest=${this.service.dest}`
        wx.navigateTo({ url })
      },
      getList () {
        list(this.service, this.page, this.pageSize.index).then(res => {
          this.list = res.data.list.map(item => {
            console.log('item', item)
            item['distTime'] = formatTime(item.time)
            console.log('distTime', item.distTime)
            item.time = formatDateTime(item.time)
            return item
          })
        })
      },
      addHandler () {
        const url = '../add/main'
        wx.navigateTo({ url })
      },
      moreHandler () {
        const url = '../list/main'
        wx.navigateTo({ url })
      },
      showUserInfo () {
        const userToken = wx.getStorageSync('user_token')
        if (!userToken) {
          wx.showToast({
            title: '请先登录',
            icon: 'none',
            duration: 1500
          })
          setTimeout(() => {
            wx.navigateTo({
              url: '/pages/login/main'
            })
          }, 1500)
        } else {
          wx.navigateTo({
            url: '/pages/my/main'
          })
        }
      },
      getShareImgUrl ({ url, tid }) {
        this.imageUrl = url
        this.tid = tid
      },
      cancelActionSheet () {
        this.imageUrl = ''
      },
      initFrom () {
        this.service = {
          type: 0,
          origin: '',
          dest: ''
        }
        this.page.pageNum = 1
        this.page.totalNum = 0
        this.tabs = [
          {
            name: '全部',
            class: 'all',
            type: 0,
            isActive: true
          },
          {
            name: '乘客',
            class: 'passenger',
            type: 1,
            isActive: false
          },
          {
            name: '车主',
            class: 'driver',
            type: 2,
            isActive: false
          }
        ]
      }
    },
    onShow () {
      const userToken = wx.getStorageSync('user_token')
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
        console.log('当前用户信息:', this.userInfo)
      }
    },
    onLoad () {
      Object.assign(this.$data, this.$options.data())
      this.getList()
    },
    onUnload () {
    },
    mounted () {
      const res = wx.getSystemInfoSync()
      const clientHeight = res.windowHeight
      const clientWidth = res.windowWidth
      this.windowHeightPx = clientHeight
      this.windowWidthPx = clientWidth
      this.canvasWidthPx = Math.ceil(clientWidth * 0.9)

      if (this.freeze) {
        this.$refs.freezeDialog.showModal('提示', '你的帐号已被冻结', false)
      } else {
        let { tid, scene } = this.$root.$mp.query
        if (scene) {
          scene = decodeURIComponent(scene)
          if (scene) {
            tid = scene.replace('&tid=', '')
          }
        }
        if (tid) {
          const url = `../detail/main?tid=${tid}`
          wx.navigateTo({ url })
        }
      }
    },
    computed: {
      ...mapGetters([
        'nickName',
        'token',
        'freeze',
        'switches',
        'pageSize',
        'swiper',
        'shareText',
        'shareImg'
      ])
    },
    onPullDownRefresh () {
      wx.showNavigationBarLoading()
      this.initFrom()
      list(this.service, this.page).then(res => {
        this.list = res.data.list.map(item => {
          console.log('item', item)
          item['distTime'] = formatTime(item.time)
          item.time = formatDateTime(item.time)
          return item
        })
        wx.stopPullDownRefresh()
        wx.hideNavigationBarLoading()
      }).catch(e => {
        wx.stopPullDownRefresh()
        wx.hideNavigationBarLoading()
      })
    },
    onShareAppMessage (res) {
      return {
        title: this.imageUrl ? this.shareText.detail : this.shareText.index,
        path: `pages/index/main?tid=${this.tid}`,
        imageUrl: this.imageUrl ? this.imageUrl : this.shareImg.index
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  @import "@/styles/mixin.scss";
  @import "@/styles/variables.scss";
  #index {
    @include height-width-100;
  }
  .main {
    width: 100%;
    .section-header {
      @include height-rpx-width-100(410);
      @include justify-align-center;
      margin-bottom: 15rpx;
      .swiper-main {
        @include height-rpx-width-100(330);
        .swiper-item {
          @include height-width-100;
          .swiper-img {
            @include height-width-100;
          }
        }
      }
      .nav-main {
        @include height-rpx-width-100(80);
        @include justify-space-between;
        background: $white;
        .nav {
          @include height-width(80, 300);
          font-size: 36rpx;
          display: flex;
          .btn {
            @include height-width-line-height-text-center(76, 100, 90);
            margin-left: 15rpx;
          }
          .active {
            width: 100rpx;
            @include border-bottom(4, $red)
          }
        }
        .my {
          /*@include height-width(75, 220);*/
           height: 80rpx;
          .user-info {
            height: 80rpx;
            @include justify-start-align-center;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            text-align: center;
            text-decoration: none;
            line-height: 80rpx;
            border-radius: 0;
            -webkit-tap-highlight-color: transparent;
            overflow: hidden;
            background-color: $white;
            .avatar-warp {
              @include height-width(60, 60);
              @include border-radius-percent(50%);
              overflow: hidden;
            }
            .avatar {
              width: 60rpx;
              height: 60rpx;
              border-radius: 50%;
              display: block;
            }
            .text {
              height: 80rpx;
              padding-left: 10rpx;
              padding-right: 20rpx;
              line-height: 80rpx;
              font-size: 36rpx;
              color: $navColor;
            }
          }
        }
      }
    }
    .search {
      @include height-rpx-width-100(300);
      @include justify-align-center;
      background: $white;
      .search-main {
        @include height-100-width-percent(94%);
        padding-top: 15rpx;
        .service {
          @include height-rpx-width-100(172);
          @include justify-start-align-center;
          .distance {
            @include height-100-width-percent(88%);
            @include justify-start-align-center;
            .info {
              @include height-percent-width-100(49%);
              @include justify-start-align-center;
              .icon {
                @include height-100-width-percent(12%);
                @include justify-align-center;
              }
              .input {
                @include height-percent-width-percent(99%, 88%);
                @include border-bottom-width(1);
                input {
                  @include height-width-100;
                  padding-left: 12rpx;
                }
              }
            }
          }
          .change {
            @include height-100-width-percent(12%);
            @include justify-align-center;
          }
        }
        .search-btn {
          @include height-rpx-width-100(113);
          @include justify-align-center;
          .search-button {
            @include height-width-percent-text-center(80);
            font-size: 32rpx;
            color: $white;
            background: $light-blue;
          }
        }
      }
    }
  }
  ::-webkit-scrollbar {
    width: 0;
    height: 0;
    color: transparent;
  }
  .list {
    width: 100%;
  }
  .add {
    @include height-width(100, 100);
    position: fixed;
    right: 40rpx;
    bottom: 50rpx;
    @include justify-align-center;
    @include border-radius(110);
    @include box-shadow;
    background: $endColor;
    text {
      @include height-width-line-height-text-center(90, 90, 75);
      font-size: 80rpx;
      color: $white;
    }
  }
</style>
