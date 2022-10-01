const useToken = (api) => {
  return (
    api.defaults.headers.Authorization?.split(" ")[1] ||
    api.defaults.headers.common?.Authorization?.split(" ")[1] ||
    null
  );
};

export default useToken;
