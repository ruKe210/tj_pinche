<template>
  <view id="map">
    <button @click="direction">路线规划</button>
    <map
      id="myMap"
      style="width: 100%; height: 300px;"
      longitude="116.313972"
      latitude="39.980014"
      scale='16'
      :polyline="polyline"
    ></map>

  </view>

</template>

<script>
  // https://lbs.qq.com/qqmap_wx_jssdk/method-direction.html
  import QQMapWX from '@/libs/qqmap-wx-jssdk'
  import { mapKey } from '@/utils/config'
  export default {
    data () {
      return {
        map: null,
        polyline: []
      }
    },
    methods: {

      direction () {
        const self = this
        this.map.direction({
          mode: 'driving',
          from: {
            latitude: 39.989221,
            longitude: 116.306076
          },
          to: {
            latitude: 39.828050,
            longitude: 116.436195
          },
          success (res) {
            console.log(res)
            // 服务异常处理
            if (res.status !== 0) {
              return
            }
            const coors = res.result.routes[0].polyline
            const pl = []
            // 坐标解压（返回的点串坐标，通过前向差分进行压缩）
            const kr = 1000000
            for (let i = 2; i < coors.length; i++) {
              coors[i] = Number(coors[i - 2]) + Number(coors[i]) / kr
            }
            // 将解压后的坐标放入点串数组pl中
            for (let j = 0; j < coors.length; j += 2) {
              pl.push({
                latitude: coors[j],
                longitude: coors[j + 1]
              })
            }
            // 设置polyline属性，将路线显示出来
            console.log(pl)
            self.polyline = [{
              points: pl,
              color: '#FF0000',
              width: 2
            }]
          },
          fail (res) {
            console.log(res)
          }
        })
      },

      driving () {
        var _this = this
        // 网络请求设置
        var opt = {
          // WebService请求地址，from为起点坐标，to为终点坐标，开发key为必填
          url: 'https://apis.map.qq.com/ws/direction/v1/driving/?from=39.989221,116.306076&to=39.828050,116.436195&key=PDKBZ-XCHLN-WUGFG-SBSKS-FNI7E-7PF3U',
          method: 'GET',
          dataType: 'json',
          // 请求成功回调
          success (res) {
            var ret = res.data
            // 服务异常处理
            if (ret.status !== 0) return
            var coors = ret.result.routes[0].polyline
            var pl = []
            // 坐标解压（返回的点串坐标，通过前向差分进行压缩）
            var kr = 1000000
            for (var i = 2; i < coors.length; i++) {
              coors[i] = Number(coors[i - 2]) + Number(coors[i]) / kr
            }
            // 将解压后的坐标放入点串数组pl中
            for (var j = 0; j < coors.length; j += 2) {
              pl.push({latitude: coors[j], longitude: coors[j + 1]})
            }
            // 设置polyline属性，将路线显示出来
            console.log(pl)
            _this.polyline = [{
              points: pl,
              color: '#FF0000',
              width: 2
            }]
          }
        }
        wx.request(opt)
      }
    },
    onLoad () {
      this.map = new QQMapWX({
        key: mapKey
      })
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  @import "@/styles/mixin.scss";
  @import "@/styles/variables.scss";
  #map {
    @include height-width-100;
    @include column-align-center;
    background: $white;
  }

</style>
