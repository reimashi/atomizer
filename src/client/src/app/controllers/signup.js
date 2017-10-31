class SignUpController {
    constructor($scope, $location, $route, apiService) {
        // Service loads
        this.$scope = $scope;
        this.$location = $location;
        this.$route = $route;
        this.api = apiService;

        if (apiService.isLoguedin()) $location.path("/");

        // Scope functions
        $scope.createUser = () => this.createUser();
    }

    $onInit() {
        // Variable definitions
        this.$scope.username = "";
        this.$scope.password = "";
        this.$scope.error = "";
    }

    /**
     * Create new user
     */
    createUser() {
        if (this.$scope.signupForm.$valid) {
            this.api.userAdd(this.$scope.username, this.$scope.password)
                .then(() => {
                    this.$location.path("/");
                    this.$route.reload();
                })
                .catch((err) => {
                    this.$scope.error = String(err)
                });
        }
        // Without else, html5 restrictions
    }
}

export { SignUpController };