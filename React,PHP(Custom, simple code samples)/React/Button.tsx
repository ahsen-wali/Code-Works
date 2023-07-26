
import Box from '@material-ui/core/Box';
import { default as MuiCircularProgress } from '@material-ui/core/CircularProgress';
import { spacing, SpacingProps } from '@material-ui/system';
import React from 'react';
import styled, { css } from 'styled-components';
import {
  black12,
  charcoalGrey,
  containedLightHover,
  dustyOrangeTwo,
  dustyOrangeTwoHover,
  Gray98,
  greyBackground,
  lightGrey,
  pinkishOrange,
  sizeDesktop,
  sizeMobile,
  turquoise,
  turquoiseHover,
  typeNavItem,
  veryLightGrey,
} from '~/styles';
import { GetComponentProps, StrictUnion } from '~/types';

const buttonSimple = css`
  ${typeNavItem};
  border: none;
  background: transparent;
  color: ${charcoalGrey};
  &:hover {
    text-decoration: underline;
  }
`;

const buttonBase = css`
  font-weight: bold;
  text-transform: uppercase;
  margin: 0;
  font-weight: 500;
  height: 51px;
  display: inline-flex;
  vertical-align: middle;
  text-align: center;
  border-radius: 2px;
  letter-spacing: 1.5px;
  font-size: 14px;
  min-width: 175px;
  padding: 2px 30px 0;
  align-items: center;
  justify-content: center;
  background-color: transparent;
  border: 2px solid transparent;
  transition: background-color 0.25s ease-in-out;
  line-height: 1.14;
  letter-spacing: 1.25px;

  &:hover {
    text-decoration: none;
  }
`;

export const buttonDefault = css`
  ${buttonBase};
  color: #fff;
  background: ${dustyOrangeTwo};
`;

const buttonResponsive = css`
  ${buttonDefault};
  height: 36px;
  min-width: auto;
  padding: 2px 20px 0;
  ${sizeDesktop(css`
    padding: 2px 15px 0;
  `)};
`;
const buttonDefaultHover = css`
  &:hover,
  &:focus {
    background-color: ${dustyOrangeTwoHover};
  }
`;
const buttonThinOrange = css`
  ${buttonBase};
  border-color: ${dustyOrangeTwo};
  background-color: transparent;
  color: ${dustyOrangeTwo};
`;
const buttonThinOrangeHover = css`
  &:hover,
  &:focus {
    background-color: ${dustyOrangeTwoHover};
  }
`;
const buttonLight = css`
  ${buttonBase};
  color: #fff;
  border-color: #fff;
`;
const buttonLightHover = css`
  &:hover,
  &:focus {
    background-color: ${greyBackground};
  }
`;
const buttonDark = css`
  ${buttonBase};
  color: ${charcoalGrey};
  border-color: ${charcoalGrey};
`;
const buttonDarkHover = css`
  &:hover,
  &:focus {
    background-color: ${greyBackground};
  }
`;
const buttonContainedHover = css`
  &:hover,
  &:focus {
    background-color: ${dustyOrangeTwoHover};
  }
`;

const buttonContained = css`
  ${buttonContainedHover}
  text-transform: uppercase;
  padding: 10px 16px;
  width: auto;
  text-align: left;
  border-radius: 4px;
  background-color: ${Gray98};
  letter-spacing: 1.25px;
  letter-spacing: 1.25px;
  font-weight: 500;
  text-align: center;
  font-size: 14px;
  color: white;
  background-color: ${dustyOrangeTwo};

  ${sizeMobile(css`
    font-size: 11px;
  `)};
`;

const buttonOutlinedHover = css`
  &:hover,
  &:focus {
    background-color: #f7f7f7;
  }
`;

const buttonOutlined = css`
  ${buttonContained}
  ${buttonOutlinedHover};
  border: solid 1px ${black12};
  background-color: transparent;
  color: ${dustyOrangeTwo};
`;

const buttonRound = css`
  display: flex;
  justify-content: center;
  border: 0;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  background-color: inherit;
  font-size: 24px;
  transition: all 0.2s;
  font-size: 24px;
  box-shadow: none;
  :hover {
    transform: scale(1.1);
    background-color: inherit;
  }
`;

const buttonContainedTurquoise = `
${buttonContained};
padding: 6px 30px;
text-transform: uppercase;
background-color: ${turquoise};
color: white;
border: solid 1px ${turquoise};
&:hover,
  &:focus {
    background-color: ${turquoiseHover};
  }
`;

const buttonContainedLight = `
${buttonContained};
text-transform: uppercase;
  padding: 10px 16px;
  width: auto;
  text-align: left;
  border-radius: 4px;
  background-color: ${Gray98};
  letter-spacing: 1.25px;
  letter-spacing: 1.25px;
  font-weight: 500;
  text-align: center;
  font-size: 14px;
  color: white;
  background-color: transparent;
  ${sizeMobile(css`
    font-size: 11px;
  `)};
  &:hover, 
  &:focus { 
    border: ${containedLightHover};
    background-color: ${containedLightHover};
  }
`;

const buttonTurquoise = css`
  ${buttonDefault}
  margin-right: 15px;
  margin-bottom: 15px;
  background: white;
  color: ${turquoise};
  :hover {
    background-color: ${greyBackground};
  }
`;

interface ButtonBase {
  kind?:
    | 'light'
    | 'thinOrange'
    | 'dark'
    | 'simple'
    | 'responsive'
    | 'contained'
    | 'containedTurquoise'
    | 'containedLight'
    | 'round'
    | 'outlined'
    | 'turquoise';
  disabled?: boolean;
  showSpinner?: boolean;
}

const CircularProgress = styled(MuiCircularProgress)`
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  color: ${pinkishOrange};
  opacity: 1;
  z-index: 2;
` as React.SFC<GetComponentProps<typeof MuiCircularProgress>>;

type ButtonElProps = JSX.IntrinsicElements['button'];
interface ButtonPropsButton extends ButtonBase, ButtonElProps {
  as?: never;
}

type AnchorElProps = JSX.IntrinsicElements['a'];
interface ButtonPropsAnchor extends ButtonBase, AnchorElProps {
  as: 'a';
}

export type ButtonProps = StrictUnion<ButtonPropsAnchor | ButtonPropsButton>;

export const buttonStyles = css`
  cursor: pointer;
  ${({ kind, disabled }: ButtonProps) => {
    switch (kind) {
      case 'light':
        return css`
          ${buttonLight};
          ${!disabled && buttonLightHover};
        `;
      case 'dark':
        return css`
          ${buttonDark};
          ${!disabled && buttonDarkHover};
        `;
      case 'simple':
        return css`
          ${buttonSimple};
        `;
      case 'responsive':
        return css`
          ${buttonResponsive};
          ${!disabled && buttonDefaultHover};
        `;
      case 'thinOrange':
        return css`
          ${buttonThinOrange};
          ${!disabled && buttonThinOrangeHover};
        `;
      case 'contained':
        return css`
          ${buttonContained};
          ${!disabled && buttonContainedHover};
        `;
      case 'containedTurquoise':
        return css`
          ${buttonContainedTurquoise};
        `;
      case 'containedLight':
        return css`
          ${buttonContainedLight};
        `;
      case 'round':
        return css`
          ${buttonRound};
        `;

      case 'outlined':
        return css`
          ${buttonOutlined};
        `;
      case 'turquoise':
        return css`
          ${buttonTurquoise};
        `;
      default:
        return css`
          ${buttonDefault};
          ${!disabled && buttonDefaultHover};
        `;
    }
  }};
  ${(p: ButtonProps) =>
    p.disabled &&
    css`
      -webkit-transition: background-color 0.5s ease-out;
      -moz-transition: background-color 0.5s ease-out;
      -o-transition: background-color 0.5s ease-out;
      transition: background-color 0.5s ease-out;
      background-color: ${veryLightGrey};
      color: ${lightGrey};
      &:hover,
      &:focus {
        cursor: not-allowed;
      }
    `};
`;

const Button_: React.SFC<ButtonProps & SpacingProps> = styled.button`
  ${spacing};
  ${buttonStyles};
` as React.SFC<ButtonProps>; // @TODO fix types

export const Button: React.SFC<ButtonProps & SpacingProps> = ({
  children,
  showSpinner,
  ...props
}) => (
  <Box position="relative">
    <Button_ {...props}>{children}</Button_>
    {showSpinner && <CircularProgress size={24} />}
  </Box>
);
