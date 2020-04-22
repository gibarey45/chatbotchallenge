import axios from "axios";
import env from "../environment/config";

const config = {
  baseURL: env.apiBaseUrl,
  headers: {
    "Accept": "application/json",
    "Content-Type": "application/json"
  }
};

const httpClient = axios.create(config);

/**
 * @param config
 * @returns {*}
 */
const authInterceptor = config => {
  /** add auth token */
  const token = localStorage.token;
  config.headers.Authorization = `Bearer ${token}`;
  return config;
};


const loggerInterceptor = config => {
  return config;
};


/** Adding the request interceptors */
httpClient.interceptors.request.use(authInterceptor);
httpClient.interceptors.request.use(loggerInterceptor);

/** Adding the response interceptors */
httpClient.interceptors.response.use(
  response => {
    return response;
  },
  error => {
    return Promise.reject(error);
  }
);

export default httpClient;
