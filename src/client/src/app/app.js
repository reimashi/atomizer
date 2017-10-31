import 'bootstrap';
import '../style/app.less';

import angular from 'angular';
import ng_router from 'angular-route';
import angular_jwt from 'angular-jwt';
import toastr from 'angular-toastr';

import {FeedController} from "./controllers/feeds";
import {SignUpController} from "./controllers/signup";
import {MainController} from "./controllers/main";
import {LoginController} from "./controllers/login";

import {ApiService} from "./services/api";

angular.module('atomizer', [ng_router, angular_jwt, toastr])
    .run(function(authManager) {
        authManager.checkAuthOnRefresh();
        authManager.redirectWhenUnauthenticated();
    })
    .filter("safehtml", ['$sce', function($sce) {
        return function(htmlCode){
            return $sce.trustAsHtml(htmlCode);
        }
    }])
    .service('apiService', ApiService)
    .factory('debugInterceptor', ($q) => { return { "request": (config) => { console.log("[TRACE] " + config.method + " => " + config.url); return config; } }; })
    .config(($routeProvider, $httpProvider, jwtOptionsProvider) => {
        $routeProvider
            .when("/", {
                templateUrl: "views/main.html",
                controller: MainController
            })
            .when("/signup", {
                templateUrl: "views/signup.html",
                controller: SignUpController
            })
            .when("/feeds", {
                templateUrl: "views/feeds.html",
                controller: FeedController
            })
            .otherwise('/');

        jwtOptionsProvider.config({
            tokenGetter: ['apiService', function(apiService) {
                return apiService.token();
            }],
            unauthenticatedRedirectPath: '/',
            whiteListedDomains: ['api.atomizer.ga', 'localhost']
        });

        $httpProvider.interceptors.push('jwtInterceptor');
        $httpProvider.interceptors.push('debugInterceptor');
    })
    .controller("mainController", MainController)
    .controller("feedController", FeedController)
    .controller("loginController", LoginController)
    .controller("signupController", SignUpController);