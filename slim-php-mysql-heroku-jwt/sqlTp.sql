-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 23-03-2021 a las 21:21:28
-- Versión del servidor: 8.0.13-4
-- Versión de PHP: 7.2.24-0ubuntu0.18.04.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pqElWX5WY2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Empleados`
--






-- TIPO EMPLEADOS
CREATE TABLE `TipoEmpleado` (
  `Id` int(11) NOT NULL,
  `Tipo` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `TipoEmpleado` (`Id`, `Tipo`) VALUES
(1, 'bartender'),
(2, 'cervecero'),
(3, 'cocinero'),
(4, 'mozo'),
(5, 'socio');

ALTER TABLE `TipoEmpleado`
  ADD PRIMARY KEY (`Id`);

ALTER TABLE `TipoEmpleado`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


-- ESTADOS EMPLEADOS
CREATE TABLE `EstadoEmpleado` (
  `Id` int(11) NOT NULL,
  `Estado` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `EstadoEmpleado` (`Id`, `Estado`) VALUES
(1, 'Activo'),
(2, 'Suspendido'),
(3, 'Inactivo');

ALTER TABLE `EstadoEmpleado`
  ADD PRIMARY KEY (`Id`);
  
ALTER TABLE `EstadoEmpleado`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
	
-- EMPLEADOS
CREATE TABLE `Empleados` (
  `Id` int(11) NOT NULL,
  `IdTipoEmpleado` int(11) NOT NULL,
  `IdEstado` int(11) NOT NULL,
  `Usuario` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Clave` varchar(250) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `Empleados` (`Id`, `IdTipoEmpleado`, `IdEstado`, `Usuario`, `Clave`) VALUES
(1, 1, 1, 'franco', 'Hsu23'),
(2, 2, 1, 'pedro', 'Qsu23'),
(3, 3, 1, 'alberto', 'Rsu23'),
(4, 4, 1, 'lucas', 'Fsu23'),
(5, 5, 1, 'ariel', 'Lsu23'),
(6, 5, 1, 'miguel', 'Ssu23'),
(7, 5, 1, 'maria', 'Ksu23'),
(8, 4, 1, 'nicolas', 'Ysu23');

ALTER TABLE `Empleados`
  ADD PRIMARY KEY (`Id`);
  
ALTER TABLE `Empleados`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
	
-- PRODUCTOS
CREATE TABLE `Productos` (
  `Id` int(11) NOT NULL,
  `IdTipoEmpleado` int(11) NOT NULL,
  `Descripcion` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Precio` float(6,2) NOT NULL,
  `MinutosPreparacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `Productos`
  ADD PRIMARY KEY (`Id`);

ALTER TABLE `Productos`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
	
-- ESTADO PEDIDOS PRODUCTOS
CREATE TABLE `EstadoPedidosProductos` (
  `Id` int(11) NOT NULL,
  `Estado` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `EstadoPedidosProductos` (`Id`, `Estado`) VALUES
(1, 'Pendiente'),
(2, 'En Preparacion'),
(3, 'Listo Para Serviir'),
(4, 'Entregado');

ALTER TABLE `EstadoPedidosProductos`
  ADD PRIMARY KEY (`Id`);
  
ALTER TABLE `EstadoPedidosProductos`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
	
-- PEDIDOS
CREATE TABLE `Pedidos` (
  `Id` int(11) NOT NULL,
  `IdMesa` int(11) NOT NULL,
  `Codigo` varchar(5) NOT NULL,
  `MinutosTotalesPreparacion` int(100) NOT NULL,
  `Foto` varchar(100) NOT NULL,
  `FechaAlta` DATETIME(6) NOT NULL,
  `FechaFin` DATETIME(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `Pedidos`
  ADD PRIMARY KEY (`Id`);
  
 ALTER TABLE `Pedidos`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;


-- PEDIDOS PRODUCTOS
CREATE TABLE `PedidosProductos` (
  `Id` int(11) NOT NULL,
  `CodigoPedido` varchar(5) NOT NULL,  
  `IdProducto` int(11) NOT NULL,
  `IdEstado` int(11) NOT NULL,
  `IdEmpleado` int(11) NOT NULL,
  `FechaAlta` DATETIME(6) NOT NULL,
  `FechaFin` DATETIME(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `PedidosProductos`
  ADD PRIMARY KEY (`Id`);
  
 ALTER TABLE `PedidosProductos`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
	
	

-- ESTADO MESAS
CREATE TABLE `EstadoMesas` (
  `Id` int(11) NOT NULL,
  `Estado` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `EstadoMesas` (`Id`, `Estado`) VALUES
(1, 'Con cliente esperando pedido'),
(2, 'Con cliente comiendo'),
(3, 'Con cliente pagando'),
(4, 'Cerrada');

ALTER TABLE `EstadoMesas`
  ADD PRIMARY KEY (`Id`);
  
ALTER TABLE `EstadoMesas`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
	
	
-- MESAS
CREATE TABLE `Mesas` (
  `Id` int(11) NOT NULL,
  `Codigo` varchar(5) NOT NULL,
  `EstadoId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `Mesas`
  ADD PRIMARY KEY (`Id`);

  ALTER TABLE `Mesas`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
  
-- ENCUESTA
CREATE TABLE `Encuestas` (
  `Id` int(11) NOT NULL,
  `IdMesa` int(11) NOT NULL,
  `IdPedido` int(11) NOT NULL,
  `IdEmpleado` int(11) NOT NULL,
  `PuntuacionMesa` int(11) NOT NULL,
  `PuntuacionRestaurante` int(11) NOT NULL,
  `PuntuacionMozo` int(11) NOT NULL,
  `PuntuacionCocinero` int(11) NOT NULL,
  `Comentarios` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `Encuestas`
  ADD PRIMARY KEY (`Id`);
  
ALTER TABLE `Encuestas`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
  
  
-- CLIENTE
CREATE TABLE `Clientes` (
  `Id` int(11) NOT NULL,
  `IdPedido` int(11) NOT NULL,
  `CodigoPedido` varchar(100) NOT NULL,
  `IdEncuesta` int(11) NOT NULL,
  `Nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;

ALTER TABLE `Clientes`
  ADD PRIMARY KEY (`Id`);

ALTER TABLE `Clientes`
	MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;