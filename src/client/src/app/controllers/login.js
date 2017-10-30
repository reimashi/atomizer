class SingUpController {
    constructor($scope, $http) {
        // Service loads
        this.$scope = $scope;

        // Scope functions
        $scope.test = () => this.test;
    }

    get scope() { return this.$scope; }

    $onInit() {
        // Variable definitions
        this.$scope.username = "";
        this.$scope.password = "";
    }

    login() {
        console.log(this.$scope);
        console.log(this.username);
        console.log(this.password);
    }
}

export { SingUpController };