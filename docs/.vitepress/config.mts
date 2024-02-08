import {defineConfig} from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "ThreadFlow",
    description: "A Laravel library enabling seamless creation of structured, interactive messenger bots",
    base: '/ThreadFlow/',
    themeConfig: {
        editLink: {
            pattern: 'https://github.com/SequentSoft/ThreadFlow/edit/master/docs/:path'
        },

        search: {
          provider: 'local'
        },

        nav: [
            {text: 'Home', link: '/'},
            {text: 'Documentation', link: '/guide/master/introduction/installation'},
        ],

        sidebar: [
            {
                text: 'Introduction',
                items: [
                    {text: 'What is ThreadFlow?', link: '/guide/master/introduction/'},
                    {text: 'Installation', link: '/guide/master/introduction/installation'},
                    {text: 'Configuration', link: '/guide/master/introduction/configuration'},
                    {text: 'How to start a bot', link: '/guide/master/introduction/starting'},
                ]
            },
            {
                text: 'Pages',
                items: [
                    {text: 'What is Pages?', link: '/guide/master/pages/'},
                    {text: 'Basic Usage', link: '/guide/master/pages/basic'},
                    {text: 'Attributes', link: '/guide/master/pages/attributes'},
                ]
            },
            {
                text: 'Messages',
                items: [
                    {text: 'Incoming', link: '/guide/master/messages/incoming'},
                    {text: 'Outgoing', link: '/guide/master/messages/outgoing'},
                    {text: 'Keyboards', link: '/guide/master/messages/keyboards'},
                    {text: 'Updating', link: '/guide/master/messages/updating'},
                ]
            },
            {
                text: 'Advanced',
                items: [
                    {text: 'Sessions', link: '/guide/master/advanced/sessions'},
                    {text: 'Dispatchers', link: '/guide/master/advanced/dispatchers'},
                    {text: 'Exceptions', link: '/guide/master/advanced/exceptions'},
                    {text: 'Testing', link: '/guide/master/advanced/testing'},
                ]
            },
            {
                text: 'Drivers',
                items: [
                    {text: 'Supported Drivers', link: '/guide/master/drivers/'},
                    {text: 'Telegram', link: '/guide/master/drivers/telegram'},
                ]
            }
        ],

        socialLinks: [
            {icon: 'github', link: 'https://github.com/SequentSoft/ThreadFlow'}
        ]
    }
})
