class SingUpController {
    constructor($scope, apiService) {
        // Service loads
        this.$scope = $scope;
        this.api = apiService;

        // Scope functions
        $scope.test = () => this.test();
    }

    $onInit() {
        // Variable definitions
        this.$scope.username = "";
        this.$scope.password = "";
    }

    test() {
        if (this.$scope.username && this.$scope.password) {
            this.api.userAdd({
                username: this.$scope.username,
                password: this.$scope.password
            })
                .then(() => {})
                .catch((err) => {});
        }
        // Without else, html5 restrictions
    }
}

export { SingUpController };