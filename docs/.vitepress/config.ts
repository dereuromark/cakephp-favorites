import { defineConfig } from 'vitepress'

const sidebar = [
  { text: 'Guide', items: [
    { text: 'Installation', link: '/guide/installation' },
    { text: 'Getting started', link: '/guide/getting-started' },
    { text: 'Stars', link: '/guide/stars' },
    { text: 'Likes and dislikes', link: '/guide/likes' },
    { text: 'Custom reactions', link: '/guide/reactions' },
    { text: 'Administration', link: '/guide/admin' },
  ] },
  { text: 'Reference', items: [
    { text: 'Configuration', link: '/reference/configuration' },
    { text: 'Upgrading', link: '/reference/upgrading' },
  ] },
]

export default defineConfig({
  title: 'CakePHP Favorites',
  description: 'Stars, votes, and custom reactions for CakePHP applications.',
  base: '/cakephp-favorites/',
  cleanUrls: true,
  lastUpdated: true,
  srcExclude: ['README.md', 'Star.md', 'Like.md', 'Favorite.md'],
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-favorites/' },
  head: [['link', { rel: 'icon', href: '/cakephp-favorites/favicon.svg', type: 'image/svg+xml' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/configuration' },
    ],
    sidebar,
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-favorites' }],
    search: { provider: 'local' },
    editLink: { pattern: 'https://github.com/dereuromark/cakephp-favorites/edit/master/docs/:path' },
    footer: { message: 'Released under the MIT License.', copyright: 'Copyright Mark Scherer' },
  },
})
