class LoginController {
    constructor($scope, $location, $route, apiService, authManager) {
        // Service loads
        this.$scope = $scope;
        this.$location = $location;
        this.$route = $route;
        this.api = apiService;
        this.authManager = authManager;

        // Scope functions
        $scope.login = () => this.login();
        $scope.logout = () => this.logout();
    }

    $onInit() {
        // Variable definitions
        this.$scope.isAuthenticated = this.authManager.isAuthenticated();
        this.$scope.username = "test";
        this.$scope.password = "test";
    }

    /**
     * Login user with form data
     */
    login() {
        let username = this.$scope.username;
        let password = this.$scope.password;

        if (!this.api.isLoguedin()) {
            if (this.$scope.loginForm.$valid) {
                this.api.login(username, password)
                    .then(() => {
                        this.$scope.isAuthenticated = this.authManager.isAuthenticated();
                        this.$route.reload();
                    })
                    .catch((err) => {
                        if (err) alert("User or password incorrect");
                        else console.error("Unknown error on login");
                    });
            }
        }
    }

    /**
     * Logout current user
     */
    logout() {
        this.api.logout();
        this.$scope.isAuthenticated = this.authManager.isAuthenticated();
        this.$location.path("/main");
    }
}

export { LoginController };