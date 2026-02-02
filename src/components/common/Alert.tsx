import { HStack, Box, Text, Icon } from '@chakra-ui/react';
import { FiAlertCircle, FiInfo, FiCheckCircle, FiAlertTriangle } from 'react-icons/fi';
import type { ReactNode } from 'react';

type Status = 'error' | 'info' | 'warning' | 'success';

interface RootProps {
  status?: Status;
  children?: ReactNode;
  className?: string;
  style?: any;
}

export const Root = ({ status = 'info', children, ...rest }: RootProps) => {
  const bg = status === 'error' ? 'red.50' : status === 'warning' ? 'yellow.50' : status === 'success' ? 'green.50' : 'blue.50';

  const borderColor = status === 'error' ? 'red.200' : status === 'warning' ? 'yellow.200' : status === 'success' ? 'green.200' : 'blue.200';

  return (
    <HStack gap={3} align="center" w="full" p={3} rounded="md" bg={bg} borderWidth={1} borderColor={borderColor} {...rest}>
      {children}
    </HStack>
  );
};

export const Indicator = ({ status = 'info' }: { status?: Status }) => {
  const color = status === 'error' ? 'red.500' : status === 'warning' ? 'yellow.500' : status === 'success' ? 'green.500' : 'blue.500';

  const icon = status === 'error' ? FiAlertCircle : status === 'warning' ? FiAlertTriangle : status === 'success' ? FiCheckCircle : FiInfo;

  return (
    <Box aria-hidden>
      <Icon as={icon} boxSize={5} color={color} />
    </Box>
  );
};

export const Title = ({ children, color }: { children?: ReactNode; color?: string }) => (
  <Text fontWeight="semibold" color={color ?? 'inherit'}>{children}</Text>
);

export default { Root, Indicator, Title };
