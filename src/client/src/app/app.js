import 'bootstrap';
import '../style/app.less';

import angular from 'angular';
import ng_router from 'angular-route';
import angular_jwt from 'angular-jwt';

import {FeedController} from "./controllers/feeds";
import {SingUpController} from "./controllers/singup";

import {ApiService} from "./services/api";

angular.module('atomizer', [ng_router, angular_jwt])
    .service('apiService', ApiService)
    .config(($routeProvider, $httpProvider, jwtOptionsProvider) => {
        $routeProvider
            .when("/", {
                templateUrl: "views/feeds.html",
                controller: FeedController
            })
            .when("/singup", {
                templateUrl: "views/singup.html",
                controller: SingUpController
            })
            .otherwise('/');

        jwtOptionsProvider.config({
            tokenGetter: ['apiService', function(apiService) {
                return apiService.token();
            }]
        });

        $httpProvider.interceptors.push('jwtInterceptor');
    })
    .controller("feedController", FeedController)
    .controller("singupController", SingUpController);