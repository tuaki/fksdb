import * as React from 'react';

import Place from './place';

import { IRoom } from '../../shared/interfaces';

interface IProps {
    info: IRoom;
}

export default class Room extends React.Component<IProps, {}> {

    public render() {
        const { info } = this.props;
        const { roomId, name, x: maxX, y: maxY } = info;
        const rows = [];
        for (let y = 0; y < maxY; y++) {
            const row = [];
            for (let x = 0; x < maxX; x++) {
                row.push(<Place
                    key={x}
                    x={x}
                    y={y}
                    roomId={roomId}
                />);
            }
            rows.push(<tr key={y}>{row}</tr>);
        }

        return (
            <div className="routing-room">
                <h3>{name}</h3>
                <table className="table">
                    <thead>
                    <tr>
                        <th colSpan={maxX} className="bg-dark text-center text-white">
                            Table
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {rows}
                    </tbody>
                </table>
            </div>
        );
    }
}
