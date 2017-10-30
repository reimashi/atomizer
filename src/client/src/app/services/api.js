class ApiService {
    constructor($rootScope, $http) {
        this.$rootScope = $rootScope;
        this.$http = $http;
    }

    token() {
        console.log("[TRACE] Apiservice.token");
        return "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ";
    }
}

export { ApiService };