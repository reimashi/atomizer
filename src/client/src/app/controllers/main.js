class MainController {
    constructor($scope, $location, authManager) {
        this.$scope = $scope;

        if (authManager.isAuthenticated()) $location.path("/feeds");
    }
}

export { MainController };