import { ChakraProvider } from '@chakra-ui/react';
import RoutesWrapper from './routes';
import theme from './theme';



function App() {
  return (
    <ChakraProvider value={theme}>
      <RoutesWrapper />
    </ChakraProvider>
  );
}

export default App;
