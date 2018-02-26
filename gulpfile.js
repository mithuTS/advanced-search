var elixir = require('laravel-elixir');

elixir.config.assetsPath = 'public/';
elixir.config.publicPath = 'public/';
elixir.config.css.outputFolder = './';

elixir(function (mix) {
    mix.less(['admin.less'],'public/css/admin.css');
});