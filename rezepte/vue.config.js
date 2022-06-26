const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  // Set as Alias in Apache config
  publicPath: '/rezepte/rezepte/dist/',
})
